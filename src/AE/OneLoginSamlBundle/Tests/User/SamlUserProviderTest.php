<?php

namespace AE\OneLoginSamlBundle\Tests\User;

use AE\OneLoginSamlBundle\Security\User\SamlUserProvider;

class SamlUserProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testLoadByUsername()
    {
        $provider = $this->getUserProvider(array('ROLE_ADMIN'));
        $user = $provider->loadUserByUsername('admin');

        $this->assertEquals('admin', $user->getUsername());
        $this->assertEquals(array('ROLE_ADMIN'), $user->getRoles());
    }

    public function testRefreshUser()
    {
        $user = $this->createMock('Symfony\Component\Security\Core\User\UserInterface');
        $provider = $this->getUserProvider();

        $this->assertSame($user, $provider->refreshUser($user));
    }

    public function testSupportsClass()
    {
        $provider = $this->getUserProvider();

        $this->assertTrue($provider->supportsClass('AE\OneLoginSamlBundle\Tests\TestUser'));
        $this->assertFalse($provider->supportsClass('Symfony\Component\Security\Core\User\UserInterface'));
    }

    protected function getUserProvider($roles = array())
    {
        return new SamlUserProvider('AE\OneLoginSamlBundle\Tests\TestUser', $roles);
    }
}
