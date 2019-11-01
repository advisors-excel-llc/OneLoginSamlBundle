<?php

namespace AE\OneLoginSamlBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see
 * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('ae_onelogin_saml');
        $rootNode    = $treeBuilder->getRootNode();

        $rootNode
            ->useAttributeAsKey('id')
            ->arrayPrototype()
                ->children()
                    ->booleanNode('strict')->end()
                    ->booleanNode('debug')->end()
                    ->scalarNode('baseurl')->end()
                    ->append($this->getIdpNode())
                    ->append($this->getSpNode())
                    ->append($this->getSecurityNode())
                    ->append($this->getContactPersonNode())
                    ->append($this->getOrganizationNode())
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    protected function getIdpNode()
    {
        $builder = new TreeBuilder('idp');
        $node    = $builder->getRootNode();

        $node
            ->children()
                ->scalarNode('entityId')->end()
                ->scalarNode('x509cert')->end()
                ->arrayNode('singleSignOnService')
                    ->children()
                        ->scalarNode('url')->end()
                        ->scalarNode('binding')->end()
                    ->end()
                ->end()
                ->arrayNode('singleLogoutService')
                    ->children()
                        ->scalarNode('url')->end()
                        ->scalarNode('binding')->end()
                    ->end()
                ->end()
                ->scalarNode('certFingerprint')->end()
                ->scalarNode('certFingerprintAlgorithm')->end()
                ->arrayNode('x509certMulti')
                    ->children()
                        ->arrayNode('signing')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('encryption')
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    protected function getSpNode()
    {
        $builder = new TreeBuilder('sp');
        $node    = $builder->getRootNode();

        $node
            ->children()
                ->scalarNode('entityId')->end()
                ->scalarNode('NameIDFormat')->end()
                ->scalarNode('x509cert')->end()
                ->scalarNode('privateKey')->end()
                ->arrayNode('assertionConsumerService')
                    ->children()
                        ->scalarNode('url')->end()
                        ->scalarNode('binding')->end()
                    ->end()
                ->end()
                ->arrayNode('attributeConsumingService')
                    ->children()
                        ->scalarNode('serviceName')->end()
                        ->scalarNode('serviceDescription')->end()
                        ->arrayNode('requestedAttributes')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('name')->end()
                                    ->booleanNode('isRequired')->defaultValue(false)->end()
                                    ->scalarNode('nameFormat')->end()
                                    ->scalarNode('friendlyName')->end()
                                    ->arrayNode('attributeValue')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('singleLogoutService')
                    ->children()
                        ->scalarNode('url')->end()
                        ->scalarNode('binding')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    protected function getSecurityNode()
    {
        $builder = new TreeBuilder('security');
        $node    = $builder->getRootNode();

        $node
            ->children()
                ->booleanNode('nameIdEncrypted')->end()
                ->booleanNode('authnRequestsSigned')->end()
                ->booleanNode('logoutRequestSigned')->end()
                ->booleanNode('logoutResponseSigned')->end()
                ->booleanNode('wantMessagesSigned')->end()
                ->booleanNode('wantAssertionsSigned')->end()
                ->booleanNode('wantAssertionsEncrypted')->end()
                ->booleanNode('wantNameId')->end()
                ->booleanNode('wantNameIdEncrypted')->end()
                ->booleanNode('requestedAuthnContext')->end()
                ->booleanNode('signMetadata')->end()
                ->booleanNode('wantXMLValidation')->end()
                ->booleanNode('lowercaseUrlencoding')->end()
                ->scalarNode('signatureAlgorithm')->end()
                ->scalarNode('digestAlgorithm')->end()
            ->end()
        ;

        return $node;
    }

    protected function getContactPersonNode()
    {
        $builder = new TreeBuilder('contactPerson');
        $node    = $builder->getRootNode();

        $node
            ->children()
                ->arrayNode('technical')
                    ->children()
                        ->scalarNode('givenName')->end()
                        ->scalarNode('emailAddress')->end()
                    ->end()
                ->end()
                ->arrayNode('support')
                    ->children()
                        ->scalarNode('givenName')->end()
                        ->scalarNode('emailAddress')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    protected function getOrganizationNode()
    {
        $builder = new TreeBuilder('organization');
        $node    = $builder->getRootNode();

        $node
            ->children()
                ->arrayNode('en')
                    ->children()
                        ->scalarNode('name')->end()
                        ->scalarNode('displayname')->end()
                        ->scalarNode('url')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
