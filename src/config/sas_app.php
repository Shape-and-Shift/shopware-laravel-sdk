<?php declare(strict_types=1);

return [
  "app_name" => env('SW_APP_NAME', 'MyApp'),
  "app_secret" => env('SW_APP_SECRET', 'MyAppSecret'),
  "registration_url" => env('SW_APP_REGISTRATION_URL', '/app-registration'),
  "confirmation_url" => env('SW_APP_CONFIRMATION_URL', '/app-registration-confirmation'),
];
