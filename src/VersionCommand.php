<?php

namespace Elkadrey\VersionBump;

use Composer\Command\BaseCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class VersionCommand extends BaseCommand
{
    protected function configure()
    {
        $this
            ->setName('version')
            ->setDescription('Bump the version like npm version')
            ->addArgument('type', InputArgument::OPTIONAL, 'patch, minor, major, prepatch, preminor, premajor')
            ->addOption('no-git', null, InputOption::VALUE_NONE, 'Skip Git commit and tag')
            ->addOption('show', null, InputOption::VALUE_NONE, 'Show the current version');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!file_exists('composer.json')) {
            $output->writeln('<error>composer.json not found in the project root.</error>');
            return 1;
        }

        $composerJson = json_decode(file_get_contents('composer.json'), true);

        // ✅ If version is missing, set default to "1.0.0"
        if (!isset($composerJson['version']) || empty($composerJson['version'])) {
            $composerJson['version'] = "1.0.0";
            file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $output->writeln('<comment>No version found in composer.json. Defaulting to 1.0.0</comment>');
        }

        // 🎯 Show current version
        if ($input->getOption('show')) {
            $output->writeln("<info>Current version: {$composerJson['version']}</info>");
            return 0;
        }

        $type = $input->getArgument('type');
        if (!$type) {
            $output->writeln('<error>Please specify a version type (patch, minor, major, prepatch, preminor, premajor).</error>');
            return 1;
        }

        $currentVersion = $composerJson['version'];

        $newVersion = $this->bumpVersion($type, $currentVersion);

        $composerJson['version'] = $newVersion;
        file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $output->writeln("<info>Version updated to: $newVersion</info>");

        if (!$input->getOption('no-git')) {
            if ($this->isGitAvailable()) {
                $this->runGitCommands($newVersion, $output);
            } else {
                $output->writeln('<comment>Git is not installed or not available. Skipping Git operations.</comment>');
            }
        } else {
            $output->writeln('<comment>Skipping Git operations (--no-git used).</comment>');
        }

        return 0;
    }

    private function bumpVersion(string $type, string $currentVersion): string
    {
        // Match normal and pre-release versions (e.g., 1.0.6, 1.0.6-beta.1)
        if (preg_match('/^(\d+)\.(\d+)\.(\d+)(?:-(alpha|beta|rc)\.?(\d+)?)?$/', $currentVersion, $matches)) {
            [$full, $major, $minor, $patch, $preType, $preNumber] = array_pad($matches, 6, null);
            $major = (int) $major;
            $minor = (int) $minor;
            $patch = (int) $patch;
            $preNumber = $preNumber !== null ? (int) $preNumber : null;

            switch ($type) {
                case 'patch':
                    return "$major.$minor." . ($patch + 1);
                case 'minor':
                    return "$major." . ($minor + 1) . ".0";
                case 'major':
                    return ($major + 1) . ".0.0";
                case 'prerelease':
                    return $this->incrementPreRelease($major, $minor, $patch, $preType, $preNumber + 1);
                case 'prepatch':
                    return $this->incrementPreRelease($major, $minor, $patch + 1, $preType, 0);
                case 'preminor':
                    return $this->incrementPreRelease($major, $minor + 1, 0, $preType, 0);
                case 'premajor':
                    return $this->incrementPreRelease($major + 1, 0, 0, $preType, 0);
                default:
                    if(preg_match('/^v?\d+(\.\d+){0,3}|^dev-/', $type))
                        return $type;
                    else throw new \InvalidArgumentException("Invalid version format in composer.json: $type");
            }
        }

        throw new \InvalidArgumentException("Invalid version format in composer.json: $currentVersion");
    }

    private function incrementPreRelease(int $major, int $minor, int $patch, ?string $preType, ?int $preNumber): string
    {
        $preType = $preType ?? 'beta'; // Default to beta if not provided
        return "$major.$minor.$patch-$preType.$preNumber";
    }





    private function runGitCommands(string $version, OutputInterface $output)
    {
        // Detect the current Git branch dynamically
        $process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln('<error>Failed to determine the current Git branch.</error>');
            return;
        }

        $currentBranch = trim($process->getOutput());

        $commands = [
            "git add composer.json",
            "git commit -m 'chore(release): bump version to $version'",
            "git tag v$version",
            "git push origin $currentBranch --tags" // 🔥 Dynamically push to the current branch
        ];

        foreach ($commands as $command) {
            $process = Process::fromShellCommandline($command);
            $process->run();

            if (!$process->isSuccessful()) {
                $output->writeln("<error>Git command failed: $command</error>");
            } else {
                $output->writeln("<info>Executed: $command</info>");
            }
        }
    }


    private function isGitAvailable(): bool
    {
        $process = new Process(['git', '--version']);
        $process->run();
        return $process->isSuccessful();
    }
}
