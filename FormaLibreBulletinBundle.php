<?php

namespace FormaLibre\BulletinBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use FormaLibre\BulletinBundle\Installation\AdditionalInstaller;


class FormaLibreBulletinBundle extends PluginBundle
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null,'bulletin');
    }

    public function hasMigrations()
    {
        return true;
    }

    public function getAdditionalInstaller()
    {
        return new AdditionalInstaller();
    }

    public function getRequiredFixturesDirectory($env){
        return 'DataFixtures/Required';
    }

    private function buildPath($file, $folder = 'suggested')
    {
        return __DIR__ . "/Resources/config/{$folder}/{$file}.yml";
    }
}

?>
