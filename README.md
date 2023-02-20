# Shopware 6 Laravel SDK

![php](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)

A Laravel package to help integrate [Shopware PHP SDK](link-shopware-php-sdk) much more easier 

## Installation

Install with Composer

```shell
composer require sas/shopware-laravel-sdk
```

Migrate shop table

```shell
php artisan migrate
```

Publish config file - Change `/config/sas_app.php` for your specific app's configuration

```shell
php artisan vendor:publish
```

```php
<?php 

/** 
 * config/sas_app.php
 * These credentials need to match with the your predefined manifest.xml 
 */
return [
  "app_name" => env('SW_APP_NAME', 'MyApp'),
  "app_secret" => env('SW_APP_SECRET', 'MyAppSecret'),
  "registration_url" => env('SW_APP_REGISTRATION_URL', '/app-registration'),
  "confirmation_url" => env('SW_APP_CONFIRMATION_URL', '/app-registration-confirmation'),
];
```

Your app is now ready to install by a Shopware application!

## Usage
- Context, ShopRepository auto-binding
- SwAppMiddleware _(alias: 'sas.app.auth')_: A middleware to verify incoming webhook requests
- SwAppIframeMiddleware _(alias: 'sas.app.auth.iframe:?app_name')_: A middleware to verify incoming requests from Iframe Shopware (`app_name` is the name of the App)
- SwAppHeaderMiddleware _(alias: 'sas.app.auth.header')_: A middleware to verify incoming requests from Headers requests

## Change log
Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contribution
Feels free to create an issue on Github issues page or contact us directly at hello@shapeandshift.dev

## Security
If you discover any security related issues, please email hello@shapeandshift.dev instead of using the issue tracker.

### Requirements
- ext-curl
- PHP 7.4 / 8.0
- vin-sw/shopware-php-sdk >= 1.0

This SDK is mainly dedicated to Shopware 6.4 and onwards, earlier SW application may still be usable without test

## Credits

- [vienthuong][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/vin-sw/shopware-sdk.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[link-packagist]: https://packagist.org/packages/vin-sw/shopware-sdk
[link-downloads]: https://packagist.org/packages/vin-sw/shopware-sdk
[link-author]: https://github.com/vienthuong
[link-contributors]: ../../contributors
[link-shopware-php-sdk]: https://github.com/vienthuong/shopware-php-sdk
