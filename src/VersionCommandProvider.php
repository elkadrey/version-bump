<?php

namespace Elkadrey\VersionBump;

use Composer\Plugin\Capability\CommandProvider;

class VersionCommandProvider implements CommandProvider
{
    public function getCommands()
    {
        return [new VersionCommand()];
    }
}
