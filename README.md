# Puzzle User Bundle

Project based on Symfony project for managing user accounts and user security.

## **Install bundle**

Open a command console, enter your project directory and execute the following command to download the latest stable version of this bundle:

```yaml
composer require webundle/puzzle-user-bundle
```

## **Step 1: Enable bundle**

Enable admin bundle by adding it to the list of registered bundles in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...
            new Puzzle\UserBundle\UserBundle(),
        );

        // ...
    }

    // ...
}
```

## **Step 2: Configure bundle Security**

Configure security by adding it in the `app/config/security.yml` file of your project:

```yaml
security:
    encoders: 
         Puzzle\UserBundle\Entity\User:
             algorithm:        sha512
             encode_as_base64: false
             iterations:       1

    role_hierarchy:
        ...
        # User
        ROLE_ACCOUNT: ROLE_ADMIN
        ROLE_SUPER_ADMIN: [..,ROLE_ACCOUNT]

    providers:
        chain_provider:
            chain:
                provider: [user_db]
        user_db:
             id: user.provider

    firewalls:
        dev:
            pattern:  ^/(_(profiler|wdt)|css|images|js)/
            security: false

        login:
            pattern: ^/login$
            anonymous: ~

        registration:
            pattern: ^/registration$
            anonymous: ~
            
        admin:
            entry_point: admin.security.authentication_entry_point
            pattern: '^%admin_prefix%'
            host: '%admin_host%'
            provider: chain_provider
            access_denied_handler: security.access_denied_handler
            form_login:
                check_path: login_check
                login_path: admin_login
                success_handler: security.authentication_success_handler
                failure_handler: security.authentication_failure_handler
                csrf_token_generator: security.csrf.token_manager
            logout:
                path: /logout
                target: admin_homepage
                delete_cookies:
                    REMEMBERME: { path: null, domain: null}
            remember_me:
                secret: "%secret%"
                lifetime: 84400
                path: admin_homepage
                domain: ~
                always_remember_me: true

        main:
            entry_point: security.authentication_entry_point
            pattern: '^/'
            host: '%host%'
            anonymous: ~
            provider: chain_provider
            access_denied_handler: security.access_denied_handler
            form_login:
                check_path: login_check
                login_path: login
                success_handler: security.authentication_success_handler
                failure_handler: security.authentication_failure_handler
                csrf_token_generator: security.csrf.token_manager
            logout:
                path: /logout
                target: app_homepage
            remember_me:
                secret: "%secret%"
                lifetime: 172 800 # 2 days
                path: app_homepage
                domain: ~
                always_remember_me: true
        
        secured_area:
            pattern:    ^/demo/secured/
            form_login:
                check_path: _security_check
                login_path: _demo_login
            logout:
                path:   _demo_logout
                target: _demo
            #anonymous: ~
            #http_basic:
            #    realm: "Secured Demo Area"

    access_control:
        ...
        # User
        - {path: ^%admin_prefix%user, host: "%admin_host%", roles: ROLE_ACCOUNT }
        - {path: ^%admin_prefix%myaccount, host: "%admin_host%", roles: ROLE_ACCOUNT }

```

## **Step 3: Enable bundle routing**

Register default routes by adding it in the `app/config/routing.yml` file of your project:

```yaml
....
user:
    resource: "@UserBundle/Resources/config/routing.yml"
    prefix:   /
```
See all user routes by typing: `php bin/console debug:router | grep user`

## **Step 4: Configure bundle**

Configure admin bundle by adding it in the `app/config/config.yml` file of your project:

```yaml
admin:
    ...
    modules_available: '..,user'
    navigation:
        nodes:
            ...
            # User
            user:
                label: 'user.title'
                description: 'user.description'
                translation_domain: 'user'
                attr:
                    class: 'fa fa-users'
                parent: ~
                user_roles: ['ROLE_ACCOUNT']
            user_list:
                label: 'user.account.navigation'
                translation_domain: 'user'
                path: 'admin_user_list'
                sub_paths: ['admin_user_create', 'admin_user_update', 'admin_user_show']
                parent: user
                user_roles: ['ROLE_ACCOUNT']
            user_group:
                label: 'user.group.navigation'
                translation_domain: 'user'
                path: 'admin_user_group_list'
                sub_paths: ['admin_user_group_create', 'admin_user_group_update', 'admin_user_group_show']
                parent: user
                user_roles: ['ROLE_ACCOUNT']

# Puzzle User configuration
user:
    registration:
        confirmation_link: true # Send confirmation url to enable account manually
        redirect_uri: '' # redirect uri after registration
        address: 'johndoe@exemple.ci' # registration address
```
