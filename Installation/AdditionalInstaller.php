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
        if (version_compare($currentVersion, '6.3.0', '<')) {
            $updater060300 = new Updater\Updater060300($this->container);
            $updater060300->setLogger($this->logger);
            $updater060300->postUpdate();
        }
        if (version_compare($currentVersion, '7.1.0', '<')) {
            $updater070100 = new Updater\Updater070100($this->container);
            $updater070100->setLogger($this->logger);
            $updater070100->postUpdate();
        }
    }
}