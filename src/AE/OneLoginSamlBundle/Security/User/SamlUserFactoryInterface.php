<?php

namespace AE\OneLoginSamlBundle\Security\User;

use AE\OneLoginSamlBundle\Security\Authentication\Token\SamlTokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface SamlUserFactoryInterface
{
    /**
     * Creates a new User object from SAML Token.
     *
     * @param SamlTokenInterface $token SAML token
     * @return UserInterface
     */
    public function createUser(SamlTokenInterface $token);
}
