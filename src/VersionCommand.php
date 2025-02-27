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
        $composerJson = json_decode(file_get_contents('composer.json'), true);

        if (!isset($composerJson['version'])) {
            $output->writeln('<error>No version field in composer.json</error>');
            return 1;
        }

        if ($input->getOption('show')) {
            $output->writeln("<info>Current version: {$composerJson['version']}</info>");
            return 0;
        }

        $type = $input->getArgument('type');
        if (!$type) {
            $output->writeln('<error>Please specify a version type (patch, minor, major, prepatch, preminor, premajor)</error>');
            return 1;
        }

        $currentVersion = $composerJson['version'];
        $newVersion = $this->bumpVersion($type, $currentVersion);

        $composerJson['version'] = $newVersion;
        file_put_contents('composer.json', json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $output->writeln("<info>Version updated to: $newVersion</info>");

        if (!$input->getOption('no-git')) {
            $this->runGitCommands($newVersion, $output);
        } else {
            $output->writeln('<comment>Skipping Git operations (--no-git used).</comment>');
        }

        return 0;
    }

    private function bumpVersion(string $type, string $currentVersion): string
    {
        $parts = explode('.', $currentVersion);
        [$major, $minor, $patch] = array_map('intval', $parts);

        switch ($type) {
            case 'patch':
                $patch++;
                break;
            case 'minor':
                $minor++;
                $patch = 0;
                break;
            case 'major':
                $major++;
                $minor = 0;
                $patch = 0;
                break;
            case 'prepatch':
                $patch++;
                return "$major.$minor.$patch-beta";
            case 'preminor':
                $minor++;
                $patch = 0;
                return "$major.$minor.$patch-beta";
            case 'premajor':
                $major++;
                $minor = 0;
                $patch = 0;
                return "$major.$minor.$patch-beta";
            default:
                throw new \InvalidArgumentException("Invalid version type: $type");
        }

        return "$major.$minor.$patch";
    }

    private function runGitCommands(string $version, OutputInterface $output)
    {
        $commands = [
            "git add composer.json",
            "git commit -m 'chore(release): bump version to $version'",
            "git tag v$version",
            "git push origin main --tags"
        ];

        foreach ($commands as $command) {
            $process = Process::fromShellCommandline($command);
            $process->run();

            if (!$process->isSuccessful()) {
                $output->writeln("<error>Git command failed: $command</error>");
            }
        }
    }
}
