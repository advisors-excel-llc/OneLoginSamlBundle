<?php

namespace AE\OneLoginSamlBundle\Tests\User;

use AE\OneLoginSamlBundle\Security\User\SamlUserFactory;
use AE\OneLoginSamlBundle\Tests\TestUser;
use AE\OneLoginSamlBundle\Security\Authentication\Token\SamlToken;

class SamlUserFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testUserMapping()
    {
        $map = array(
            'password' => 'notused',
            'email' => '$mail',
            'name' => '$cn',
            'lastname' => '$sn',
            'roles' => ['ROLE_USER']
        );

        $token = $this->createMock(SamlToken::class);
        $token->method('getUsername')->willReturn('admin');
        $token->method('getAttributes')->willReturn(array(
            'mail' => array('email@mail.com'),
            'cn' => array('testname'),
            'sn' => array('testlastname')
        ));

        $factory = new SamlUserFactory(TestUser::class, $map);
        $user = $factory->createUser($token);

        $this->assertEquals('admin', $user->getUsername());
        $this->assertEquals('email@mail.com', $user->getEmail());
        $this->assertEquals('testname', $user->getName());
        $this->assertEquals('testlastname', $user->getLastname());
        $this->assertEquals('notused', $user->getPassword());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

}
