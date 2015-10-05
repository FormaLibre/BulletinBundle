<?php

namespace FormaLibre\BulletinBundle;

use Claroline\CoreBundle\Library\PluginBundle;
use Claroline\KernelBundle\Bundle\ConfigurationBuilder;
use Claroline\KernelBundle\Bundle\ConfigurationProviderInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;


class FormaLibreBulletinBundle extends PluginBundle implements ConfigurationProviderInterface
{
    public function getConfiguration($environment)
    {
        $config = new ConfigurationBuilder();
        return $config->addRoutingResource(__DIR__.'/Resources/config/routing.yml', null,'bulletin');
    }

    public function hasMigrations()
    {
        return false;
    }

    public function getRequiredFixturesDirectory($env){
        return 'DataFixtures/Required';
    }

    public function suggestConfigurationFor(Bundle $bundle, $environment)
    {
        $config = new ConfigurationBuilder();
        $config
            ->addContainerResource($this->buildPath('knp_snappy'));

        return $config;

    }

    private function buildPath($file, $folder = 'suggested')
    {
        return __DIR__ . "/Resources/config/{$folder}/{$file}.yml";
    }
}

?>
