<?php

namespace FormaLibre\BulletinBundle\Installation;

use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    public function postUpdate($currentVersion, $targetVersion)
    {
        if (version_compare($currentVersion, '6.1.1', '<')) {
            $updater020200 = new Updater\Updater060101($this->container);
            $updater020200->setLogger($this->logger);
            $updater020200->postUpdate();
        }
    }
}