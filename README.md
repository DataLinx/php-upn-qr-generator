# UPN QR code generator for PHP

![Packagist Version](https://img.shields.io/packagist/v/datalinx/php-upn-qr-generator)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/datalinx/php-upn-qr-generator)
![Coverage 100%](assets/coverage.svg)
![Packagist License](https://img.shields.io/packagist/l/datalinx/php-upn-qr-generator)
![Packagist Downloads](https://img.shields.io/packagist/dt/datalinx/php-upn-qr-generator)

## About
Using this library you can generate a QR code for a UPN payment order, which is used in Slovenia. The technical specification is defined by the Slovenian Bank Association.

This library can output a PNG, SVG or EPS image to a local file.

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
In order to generate a QR code you need to pass at least these parameters:
* recipientIban
* recipientCity

The minimal required parameters are based on the Android applications ISPS and Banka IN offered by the Intesa Sanpaolo Bank Slovenia. 

See class docblocks for accepted parameter formats and length.

### Sample code
```php
// Create an instance of the UPNQR class
$QR = new UPNQR();

// Set parameters
$QR->setPayerReference("SI00225268-32526-222");
$QR->setPayerName("Janez Novak");
$QR->setPayerStreetAddress("Lepa ulica 33");
$QR->setPayerCity("Koper");
$QR->setAmount(55.58);
$QR->setPaymentDate("2022-06-16");
$QR->setUrgent(false);
$QR->setPurposeCode("GDSV");
$QR->setPaymentPurpose("Predračun 111");
$QR->setRecipientIban("SI56020360253863406");
$QR->setRecipientReference("SI081236-17-34565");
$QR->setRecipientName("Podjetje d.o.o.");
$QR->setRecipientStreetAddress("Neka ulica 5");
$QR->setRecipientCity("Ljubljana");

try {
    // Generate QR code image of type svg (use .png for PNG images)
    $QR->generateQrCode("./qrcode.svg");
} catch (Exception $e) {
    throw new Exception("Error generating QR code image: " . $e->getMessage());
}

```

## Contributing
If you have some suggestions how to make this package better, please open an issue or even better, submit a pull request.

The project adheres to the PSR-4 and PSR-12 standards.

The code is fully tested, including OCRing of the generated QR code.

### Developer documentation
* [QR code technical specification](https://upn-qr.si/uploads/files/Tehnicni%20standard%20UPN%20QR.pdf) (see chapter 5.2)
