<?php
/**
 * Created by PhpStorm.
 * User: joerg
 * Date: 14.02.16
 * Time: 19:38
 */
namespace Bingemer\InwxBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;

class BingemerInwxExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        //config
//        $configuration = new Configuration();
//        $config = $this->processConfiguration($configuration, $configs);
        //service
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );
        $loader->load('services.yml');
    }
}