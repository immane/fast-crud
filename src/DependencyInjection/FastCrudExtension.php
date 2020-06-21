<?php
declare(strict_types=1);

namespace RinProject\FastCrudBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class FastCrudExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        // set parameters
        $container->setParameter('fast_crud.exception_interceptor', $config['exception_interceptor']);
    }
}
