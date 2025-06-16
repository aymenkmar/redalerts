<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SSO Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for Single Sign-On (SSO) functionality.
    | You can manage which users and domains are allowed to use SSO authentication.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Allowed SSO Emails
    |--------------------------------------------------------------------------
    |
    | List of specific email addresses that are allowed to use SSO.
    | These emails will be able to authenticate using Microsoft Azure AD.
    |
    */
    'allowed_emails' => [
        'aymen.kmar@satoripop.com',
        // Add more specific emails here
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed SSO Domains
    |--------------------------------------------------------------------------
    |
    | List of email domains that are allowed to use SSO.
    | Any email from these domains will be able to authenticate using SSO.
    |
    */
    'allowed_domains' => [
        'satoripop.com',
        // Add more domains here
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-create Users
    |--------------------------------------------------------------------------
    |
    | Whether to automatically create user accounts for SSO users who don't
    | already exist in the system. If false, users must be manually created
    | before they can use SSO.
    |
    */
    'auto_create_users' => true,

    /*
    |--------------------------------------------------------------------------
    | Default User Role
    |--------------------------------------------------------------------------
    |
    | The default role to assign to auto-created SSO users.
    | This can be used if you have a role-based permission system.
    |
    */
    'default_role' => 'user',

    /*
    |--------------------------------------------------------------------------
    | SSO Provider Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the SSO provider (Azure AD).
    |
    */
    'provider' => [
        'name' => 'Microsoft',
        'scopes' => ['openid', 'profile', 'email'],
        'tenant_type' => 'common', // 'common', 'organizations', 'consumers', or specific tenant ID
    ],
];
