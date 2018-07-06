# OneloginSamlBundle
OneLogin SAML Bundle for Symfony. (https://github.com/onelogin/php-saml)

Installation
------------

Install with composer
``` json
"require": {
    "ae/oneloginsaml-bundle": "dev-master"
}
```

Run composer update
``` bash
composer update ae/oneloginsaml-bundle
```

Enable the bundle in `app/AppKernel.php`
``` php
$bundles = array(
    // ...
    new AE\OneLoginSamlBundle\AEOneLoginSamlBundle(),
)
```

Configuration
-------------

Configure SAML metadata in `app/config/config.yml`. Check https://github.com/onelogin/php-saml#settings for more info.
``` yml
ae_one_login_saml:
    # default is the config name, but can be anything. This is used in firewall and route config
    default:
        # Basic settings
        idp:
            entityId: 'http://id.example.com/saml2/idp/metadata.php'
            singleSignOnService:
                url: 'http://id.example.com/saml2/idp/SSOService.php'
                binding: 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
            singleLogoutService:
                url: 'http://id.example.com/saml2/idp/SingleLogoutService.php'
                binding: 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
            x509cert: ''
        sp:
            entityId: 'http://myapp.com/app_dev.php/saml/metadata'
            assertionConsumerService:
                url: 'http://myapp.com/app_dev.php/saml/acs'
                binding: 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST'
            singleLogoutService:
                url: 'http://myapp.com/app_dev.php/saml/logout'
                binding: 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect'
        # Optional settings
        security:
            nameIdEncrypted:       false
            authnRequestsSigned:   false
            logoutRequestSigned:   false
            logoutResponseSigned:  false
            wantMessagesSigned:    false
            wantAssertionsSigned:  false
            wantNameIdEncrypted:   false
            requestedAuthnContext: true
            signMetadata: false
            wantXMLValidation: true
            signatureAlgorithm: 'http://www.w3.org/2000/09/xmldsig#rsa-sha1'
            digestAlgorithm: 'http://www.w3.org/2000/09/xmldsig#sha1'
        contactPerson:
            technical:
                givenName: 'Tech User'
                emailAddress: 'techuser@example.com'
            support:
                givenName: 'Support User'
                emailAddress: 'supportuser@example.com'
        organization:
            en:
                name: 'Example'
                displayname: 'Example'
                url: 'http://example.com'
```

If you don't want to set contactPerson or organization, don't add those parameters instead of leaving them blank.

Configure firewall and user provider in `app/config/security.yml`
``` yml
security:
    # ...

    providers:
        saml_provider:
            # Basic provider instantiates a user with default roles
            saml:
                user_class: 'AppBundle\Entity\User'
                default_roles: ['ROLE_USER']

    firewalls:
        app:
            pattern:    ^/
            anonymous: true
            saml:
                # Tell the firewall which SAML config to use, defaults to 'default'
                config: default
                # Match SAML attribute 'uid' with username.
                # Uses getNameId() method by default.
                username_attribute: 
                # check_path and login_path don't need to be specified unless overridding
                # check_path: /saml/default/acs
                # login_path: /saml/default/login
            logout:
                # Path should be /saml/[CONFIG NAME]/logout
                path: /saml/default/logout

    access_control:
        - { path: ^/saml/default/login, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/saml/default/metadata, roles: IS_AUTHENTICATED_ANONYMOUSLY }
        - { path: ^/, roles: ROLE_USER }
```

Edit your `app/config/routing`
``` yml
ae_saml_sp:
    resource: "@AEOneLoginSamlBundle/Resources/config/routing.yml"
```

Inject SAML attributes into User object (Optional)
--------------------------------------------------
Your user class must implement `SamlUserInterface`

``` php
<?php

namespace AppBundle\Entity;

use AE\OneLoginSamlBundle\Security\User\SamlUserInterface;

class User implements SamlUserInterface
{
    protected $username;
    protected $email;

    // ...

    public function setSamlAttributes(array $attributes)
    {
        $this->email = $attributes['mail'][0];
    }
}
```

Then you can get attributes from user object
``` php
$email = $this->getUser()->getEmail();
```

Integration with classic login form
-----------------------------------

You can integrate SAML authentication with traditional login form by editing your `security.yml`:

``` yml
security:
    providers:
        user_provider:
            # Loads user from user repository
            entity:
                class: AppBundle:User
                property: username

    firewalls:
        default:
            anonymous: ~
            saml:
                # Tell the firewall which SAML Config you want to use (defaults to 'default')
                config: default
                username_attribute: uid
                # No need to specify check_path and login_path, they're set based on config name
                failure_path: /login
                always_use_default_target_path: true

            # Traditional login form
            form_login:
                login_path: /login
                check_path: /login_check
                always_use_default_target_path: true

            logout:
                path: /saml/default/logout
```

Then you can add a link to route `saml_login` in your login page in order to start SAML sign on.

``` html
    <a href="{{ path('saml_login', {saml: 'default'}) }}">SAML Login</a>
```

Just-in-time user provisioning (optional)
-----------------------------------------

When user is not found by user provider, you can set a user factory to create a new user mapping SAML attributes.

Edit firewall settings in `security.yml`:

``` yml
firewalls:
    default:
        anonymous: ~
        saml:
            # Tell the firewall which config to use (default is the default)
            config: default
            username_attribute: uid
            # User factory service
            user_factory: my_user_factory
            # Persist new user. Doctrine is required.
            persist_user: true
        logout:
            path: /saml/logout
```

Create the user factory service editing `services.yml`:

``` yml
services:
    my_user_factory:
        class: AE\OneLoginSamlBundle\Security\User\SamlUserFactory
        arguments:
            # User class
            - AppBundle\Entity\User
            # Attribute mapping.
            - password: 'notused'
              email: $mail
              name: $cn
              lastname: $sn
              roles: ['ROLE_USER']
```

Fields with '$' references to SAML attribute value.

Or you can create your own User Factory that implements `SamlUserFactoryInterface`

``` php
<?php

namespace AppBundle\Security;

use AppBundle\Entity\User;
use AE\OneLoginSamlBundle\Security\Authentication\Token\SamlTokenInterface;
use AE\OneLoginSamlBundle\Security\User\SamlUserFactoryInterface;

class UserFactory implements SamlUserFactoryInterface
{
    public function createUser(SamlTokenInterface $token)
    {
        $attributes = $token->getAttributes();
        $user = new User();
        $user->setRoles(array('ROLE_USER'));
        $user->setUsername($token->getUsername());
        $user->setPassword('notused');
        $user->setEmail($attributes['mail'][0]);
        $user->setName($attributes['cn'][0]);

        return $user;
    }
}
```

``` yml
services:
    my_user_factory:
        class: AppBundle\Security\UserFactory
```
