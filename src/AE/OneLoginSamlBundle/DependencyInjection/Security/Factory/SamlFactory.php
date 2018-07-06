<?php

namespace AE\OneLoginSamlBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Reference;

class SamlFactory extends AbstractFactory
{
    public function __construct()
    {
        $this->addOption('config', 'default');
        $this->addOption('username_attribute');
        $this->addOption('check_path');
        $this->addOption('user_factory');
        $this->addOption('token_factory');
        $this->addOption('persist_user', false);

        $this->defaultFailureHandlerOptions['login_path'] = '/saml/login';
    }

    /**
     * Defines the position at which the provider is called.
     * Possible values: pre_auth, form, http, and remember_me.
     *
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'saml';
    }

    /**
     * @return string
     */
    protected function getListenerId()
    {
        return 'ae_onelogin_saml.saml_listener';
    }

    /**
     * Subclasses must return the id of a service which implements the
     * AuthenticationProviderInterface.
     *
     * @param ContainerBuilder $container
     * @param string $id             The unique id of the firewall
     * @param array $config          The options array for this listener
     * @param string $userProviderId The id of the user provider
     *
     * @return string never null, the id of the authentication provider
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $providerId          = 'security.authentication.provider.saml.'.$id;
        $definitionClassname = $this->getDefinitionClassname();
        $definition          = $container->setDefinition(
            $providerId,
            new $definitionClassname('ae_onelogin_saml.saml_provider')
        )
                                         ->setArgument(0, new Reference($userProviderId))
                                         ->setArgument(
                                             1,
                                             [
                                                 'persist_user' => $config['persist_user'],
                                             ]
                                         )
                                         ->addTag('ae.saml_provider')
        ;

        if ($config['user_factory']) {
            $definition->addMethodCall('setUserFactory', [new Reference($config['user_factory'])]);
        }

        $factoryId = $config['token_factory'] ?: 'ae_onelogin_saml.saml_token_factory';
        $definition->addMethodCall('setTokenFactory', [new Reference($factoryId)]);

        return $providerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param $id
     * @param $config
     * @param $userProvider
     *
     * @return string
     */
    protected function createListener($container, $id, $config, $userProvider)
    {
        if (!array_key_exists('check_path', $config) || strlen($config['check_path']) == 0) {
            $config['check_path'] = "/saml/${config['config']}/acs";
        }

        if (!array_key_exists('login_path', $config) || strlen($config['login_path']) == 0
            || $config['login_path'] === $this->defaultFailureHandlerOptions['login_path']) {
            $config['login_path'] = "/saml/${config['config']}/login";
        }

        $listenerId = parent::createListener($container, $id, $config, $userProvider);
        $this->createLogoutHandler($container, $id, $config);

        $container->getDefinition($listenerId)
                  ->addMethodCall(
                      'setOneLoginAuth',
                      [new Reference("ae_onelogin_saml.${config['config']}.auth")]
                  )
        ;

        return $listenerId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string $id
     * @param array $config
     * @param string $defaultEntryPoint
     *
     * @return string
     */
    protected function createEntryPoint($container, $id, $config, $defaultEntryPoint)
    {
        $entryPointId        = 'security.authentication.form_entry_point.'.$id;
        $definitionClassname = $this->getDefinitionClassname();
        $container
            ->setDefinition($entryPointId, new $definitionClassname('security.authentication.form_entry_point'))
            ->addArgument(new Reference('security.http_utils'))
            ->addArgument($config['login_path'])
            ->addArgument($config['use_forward'])
        ;

        return $entryPointId;
    }

    /**
     * @param ContainerBuilder $container
     * @param $id
     * @param $config
     */
    protected function createLogoutHandler($container, $id, $config)
    {
        if ($container->hasDefinition('security.logout_listener.'.$id)) {
            $logoutListener = $container->getDefinition('security.logout_listener.'.$id);
            $samlListenerId = "ae_onelogin_saml.${config['config']}.saml_logout";
            $logoutListener->addMethodCall('addHandler', [new Reference($samlListenerId)]);
        }
    }

    /**
     * @return string
     */
    private function getDefinitionClassname()
    {
        return class_exists(ChildDefinition::class) ? ChildDefinition::class : DefinitionDecorator::class;
    }
}
