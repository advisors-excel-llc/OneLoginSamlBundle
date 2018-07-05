<?php

namespace AE\OneLoginSamlBundle;

use AE\OneLoginSamlBundle\DependencyInjection\Compiler\SecurityCompilerPass;
use AE\OneLoginSamlBundle\DependencyInjection\Security\Factory\SamlFactory;
use AE\OneLoginSamlBundle\DependencyInjection\Security\Factory\SamlUserProviderFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AEOneLoginSamlBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new SamlFactory());
        $extension->addUserProviderFactory(new SamlUserProviderFactory());

        $container->addCompilerPass(new SecurityCompilerPass());
    }
}
