# UPN QR code generator for PHP

![Packagist Version](https://img.shields.io/packagist/v/datalinx/php-upn-qr-generator)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/datalinx/php-upn-qr-generator)
![Coverage 100%](assets/coverage.svg)
![Packagist License](https://img.shields.io/packagist/l/datalinx/php-upn-qr-generator)
![Packagist Downloads](https://img.shields.io/packagist/dt/datalinx/php-upn-qr-generator)

## About
Using this library you can generate a QR code for a UPN payment order, which is used in Slovenia. The technical specification is defined by the Slovenian Bank Association.

This library can output a PNG or SVG image to a local file.

See the changelog [here](CHANGELOG.md).

## Requirements
- PHP >= 7.4
- mbstring PHP extension

## Installing
Download it with composer:
```shell
composer require datalinx/php-upn-qr-generator
````

## Usage
```php

```

## Contributing
If you have some suggestions how to make this package better, please open an issue or even better, submit a pull request.

The project adheres to the PSR-4 and PSR-12 standards.

The code is fully tested, including OCRing of the generated QR code.

### Developer documentation
* [QR code technical specification](https://upn-qr.si/uploads/files/Tehnicni%20standard%20UPN%20QR.pdf) (see chapter 5.2)
