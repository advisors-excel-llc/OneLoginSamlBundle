<?php

namespace AE\OneLoginSamlBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class SecurityCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('doctrine.orm.default_entity_manager')) {
            foreach (array_keys($container->findTaggedServiceIds('ae.saml_provider')) as $id) {
                $container->getDefinition($id)
                          ->addMethodCall(
                              'setEntityManager',
                              array(new Reference('doctrine.orm.default_entity_manager'))
                          );
            }
        }
    }


}
