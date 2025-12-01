<?php

return [
    'enabled' => env('SAML_ENABLED', false),
    'employee_number_attribute' => env('SAML_EMPLOYEE_ATTR', 'employeeNumber'),

    // IdP names map to files in config/saml2/{idpName}_idp_settings.php
    'idpNames' => [env('SAML_IDP_NAME', 'okta')],

    // We register custom routes, so keep the package routes off.
    'useRoutes' => false,
    'routesPrefix' => '/saml',
    'routesMiddleware' => ['web'],

    'retrieveParametersFromServer' => false,
    'logoutRoute' => '/',
    'loginRoute' => '/dashboard',
    'errorRoute' => '/login',
    'proxyVars' => env('SAML_PROXY_VARS', false),
];
