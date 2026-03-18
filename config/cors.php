<?php

/*
|--------------------------------------------------------------------------
| config/cors.php
|
| Allows the React frontend (Vite dev server on :3000, or any origin
| in production) to call the Laravel JSON API with cookies.
|--------------------------------------------------------------------------
*/

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
     | During development set this to your Vite dev server.
     | In production replace with your actual domain.
     |
     | NEVER use '*' when allow_credentials is true — browsers will block it.
     */
    'allowed_origins' => [
    'http://localhost:3000',
    'https://vox-alumni-frontend-axgeje1es-soufianes-projects-ad3dd67d.vercel.app/',  // your InfinityFree URL
],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    /*
     | Must be true so the browser sends the vox_token cookie
     | with every API request (anonymous visitor identity).
     */
    'supports_credentials' => true,

];
