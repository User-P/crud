<?php

$appUrl = rtrim(env('APP_URL', 'http://localhost'), '/');

return [
    'strict' => true,
    'debug' => env('APP_DEBUG', false),
    'sp' => [
        'entityId' => env('SAML_SP_ENTITY_ID', $appUrl . '/saml/metadata'),
        'assertionConsumerService' => [
            'url' => env('SAML_SP_ACS', $appUrl . '/saml/acs'),
        ],
        'singleLogoutService' => [
            'url' => env('SAML_SP_SLS', $appUrl . '/saml/sls'),
        ],
        'NameIDFormat' => env('SAML_SP_NAMEID_FORMAT', 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'),
        'x509cert' => env('SAML_SP_CERT', ''),
        'privateKey' => env('SAML_SP_PRIVATE_KEY', ''),
    ],

    'idp' => [
        'entityId' => env('SAML_IDP_ENTITY_ID', ''),
        'singleSignOnService' => [
            'url' => env('SAML_IDP_SSO_URL', ''),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        'singleLogoutService' => [
            'url' => env('SAML_IDP_SLO_URL', ''),
            'binding' => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect',
        ],
        'x509cert' => env('SAML_IDP_CERT', ''),
    ],

    'security' => [
        'nameIdEncrypted' => false,
        'authnRequestsSigned' => env('SAML_SIGN_REQUESTS', true),
        'logoutRequestSigned' => env('SAML_SIGN_REQUESTS', true),
        'logoutResponseSigned' => env('SAML_SIGN_REQUESTS', true),
        'signMetadata' => true,
        'wantMessagesSigned' => env('SAML_WANT_MESSAGES_SIGNED', true),
        'wantAssertionsSigned' => env('SAML_WANT_ASSERTIONS_SIGNED', true),
        'wantAssertionsEncrypted' => env('SAML_WANT_ASSERTIONS_ENCRYPTED', true),
        'wantNameIdEncrypted' => false,
        'requestedAuthnContext' => false,
        'signatureAlgorithm' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
        'digestAlgorithm' => 'http://www.w3.org/2001/04/xmlenc#sha256',
    ],

    'contactPerson' => [
        'technical' => [
            'givenName' => env('SAML_CONTACT_TECHNICAL_NAME', ''),
            'emailAddress' => env('SAML_CONTACT_TECHNICAL_EMAIL', ''),
        ],
        'support' => [
            'givenName' => env('SAML_CONTACT_SUPPORT_NAME', ''),
            'emailAddress' => env('SAML_CONTACT_SUPPORT_EMAIL', ''),
        ],
    ],

    'organization' => [
        'en-US' => [
            'name' => env('SAML_ORG_NAME', env('APP_NAME', 'Laravel')),
            'displayname' => env('SAML_ORG_DISPLAY_NAME', env('APP_NAME', 'Laravel')),
            'url' => env('APP_URL'),
        ],
    ],
];
