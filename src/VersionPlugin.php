<?php

namespace Elkadrey\VersionBump;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\Capable;
use Composer\Plugin\Capability\CommandProvider;

class VersionPlugin implements PluginInterface, Capable
{
    public function activate(Composer $composer, IOInterface $io)
    {
        $io->write("<info>VersionBump Plugin activated!</info>");
    }

    public function deactivate(Composer $composer, IOInterface $io) {}

    public function uninstall(Composer $composer, IOInterface $io) {}

    public function getCapabilities()
    {
        return [
            CommandProvider::class => VersionCommandProvider::class,
        ];
    }
}
