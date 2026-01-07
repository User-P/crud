<?php

return [
    'enabled' => env('SAML_ENABLED', false),
    'employee_number_attribute' => env('SAML_EMPLOYEE_ATTR', 'employeeNumber'),
    'idpNames' => [env('SAML_IDP_NAME', 'okta')],
    'useRoutes' => false,
    'routesPrefix' => '/saml',
    'routesMiddleware' => ['web'],
    'retrieveParametersFromServer' => false,
    'logoutRoute' => '/',
    'loginRoute' => '/seguimientos',
    'errorRoute' => '/login',
    'proxyVars' => env('SAML_PROXY_VARS', false),
];
