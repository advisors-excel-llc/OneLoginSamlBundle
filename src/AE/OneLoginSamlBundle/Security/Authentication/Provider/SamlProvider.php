<?php

namespace AE\OneLoginSamlBundle\Security\Authentication\Provider;

use AE\OneLoginSamlBundle\Security\Authentication\Token\SamlTokenFactoryInterface;
use AE\OneLoginSamlBundle\Security\Authentication\Token\SamlTokenInterface;
use AE\OneLoginSamlBundle\Security\User\SamlUserFactoryInterface;
use AE\OneLoginSamlBundle\Security\User\SamlUserInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SamlProvider implements AuthenticationProviderInterface
{
    private $userProvider;
    /** @var SamlUserFactoryInterface */
    private $userFactory;
    /** @var SamlTokenFactoryInterface */
    private $tokenFactory;
    /** @var EntityManager */
    private $entityManager;
    private $options;

    public function __construct(UserProviderInterface $userProvider, array $options = array())
    {
        $this->userProvider = $userProvider;
        $this->options = array_merge(array(
            'persist_user' => false
        ), $options);
    }

    public function setUserFactory(SamlUserFactoryInterface $userFactory)
    {
        $this->userFactory = $userFactory;
    }

    public function setTokenFactory(SamlTokenFactoryInterface $tokenFactory)
    {
        $this->tokenFactory = $tokenFactory;
    }

    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->retrieveUser($token);

        if ($user) {
            if ($user instanceof SamlUserInterface) {
                $user->setSamlAttributes($token->getAttributes());
            }
            
            $authenticatedToken = $this->tokenFactory->createToken($user, $token->getAttributes(), $user->getRoles());
            $authenticatedToken->setAuthenticated(true);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof SamlTokenInterface;
    }

    protected function retrieveUser(TokenInterface $token)
    {
        try {
            return $this->userProvider->loadUserByUsername($token->getUsername());
        } catch (UsernameNotFoundException $e) {
            if ($this->userFactory instanceof SamlUserFactoryInterface) {
                return $this->generateUser($token);
            }
            
            throw $e;
        }
    }

    protected function generateUser($token)
    {
        $user = $this->userFactory->createUser($token);

        if ($this->options['persist_user'] && $this->entityManager) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }
}
