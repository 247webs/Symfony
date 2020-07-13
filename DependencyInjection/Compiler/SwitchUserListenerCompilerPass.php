<?php

namespace AppBundle\DependencyInjection\Compiler;

use AppBundle\EventListener\SwitchUserListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class SwitchUserListenerCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition('security.authentication.switchuser_listener');
        $definition->setClass(SwitchUserListener::class);
    }
}
