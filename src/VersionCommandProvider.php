<?php

namespace Elkadrey\VersionBump;

use Composer\Plugin\Capability\CommandProvider;
use Composer\Command\BaseCommand;

class VersionCommandProvider implements CommandProvider
{
    public function getCommands()
    {
        return [new VersionCommand()];
    }
}
