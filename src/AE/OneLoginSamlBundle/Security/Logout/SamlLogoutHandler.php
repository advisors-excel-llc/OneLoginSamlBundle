<?php

namespace AE\OneLoginSamlBundle\Security\Logout;

use AE\OneLoginSamlBundle\Security\Authentication\Token\SamlTokenInterface;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Error;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class SamlLogoutHandler implements LogoutHandlerInterface
{
    /**
     * @var Auth
     */
    protected $samlAuth;

    public function __construct(Auth $samlAuth)
    {
        $this->samlAuth = $samlAuth;
    }

    /**
     * This method is called by the LogoutListener when a user has requested
     * to be logged out. Usually, you would unset session variables, or remove
     * cookies, etc.
     *
     * @param Request $request
     * @param Response $response
     * @param TokenInterface $token
     */
    public function logout(Request $request, Response $response, TokenInterface $token)
    {
        if (!$token instanceof SamlTokenInterface) {
            return;
        }

        try {
            $this->samlAuth->processSLO();
        } catch (Error $e) {
            $sessionIndex = $token->hasAttribute('sessionIndex') ? $token->getAttribute('sessionIndex') : null;
            $this->samlAuth->logout(null, array(), $token->getUsername(), $sessionIndex);
        }
    }
}
