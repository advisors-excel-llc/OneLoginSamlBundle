<?php

namespace AE\OneLoginSamlBundle\DependencyInjection;

use AE\OneLoginSamlBundle\Security\Firewall\SamlListener;
use AE\OneLoginSamlBundle\Security\Logout\SamlLogoutHandler;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class AEOneLoginSamlExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        foreach ($config['ae_saml_sp'] as $conf) {
            $this->createOneLoginAuth($container, $conf);
            $this->createlSamlLogoutListener($container, $conf['id']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('ae_onelogin_saml.settings', $config);
    }

    private function createOneLoginAuth(ContainerBuilder $container, array $config)
    {
        $def = new Definition(\OneLogin_Saml2_Auth::class, [$config]);
        $def->setPrivate(false);

        $container->setDefinition("ae_onelogin_saml.${config['id']}.auth", $def);
    }

    private function createlSamlLogoutListener(ContainerBuilder $container, $cid)
    {
        $def = new Definition(SamlLogoutHandler::class, [new Reference("ae_onelogin_saml.$cid.auth")]);

        $container->setDefinition("ae_onelogin_saml.$cid.saml_logout", $def);
    }
}
