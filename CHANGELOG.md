# Changelog

## [1.1.2](https://github.com/DataLinx/php-upn-qr-generator/compare/v1.1.1...v1.1.2) (2023-02-10)


### Bug Fixes

* fix deprecated notices with PHP 8.1 ([d494601](https://github.com/DataLinx/php-upn-qr-generator/commit/d494601878215a2e554259cbdc7f5f8855ab1ccc))
* fix payment date validation ([4f5a96d](https://github.com/DataLinx/php-upn-qr-generator/commit/4f5a96d5029faea59ad5de326c87de72ece2b28b))


### Miscellaneous Chores

* fix typo for Bacon QR code generator ([41e452e](https://github.com/DataLinx/php-upn-qr-generator/commit/41e452e06c4c74f406a2f374c564a2ae59c877fd))
* remove composer.lock from VCS ([020502e](https://github.com/DataLinx/php-upn-qr-generator/commit/020502e87c1df64c313c7693a149e516c9041937))
* update .gitattributes to export-ignore more directories and files ([e3d4cc2](https://github.com/DataLinx/php-upn-qr-generator/commit/e3d4cc236a5d56fc870d383dc8961c2894af4a89))

## 1.1.1 (2022-09-21)
### Changed
- Fixed problems from code inspection

## 1.1.0 (2022-09-09)
### Changed
- Make some parameters non-required
- Improve purposeCode setter
- Add note regarding OCR bug when testing
- Make amount parameter nullable, add nullable test placeholder
- Made not required parameters nullable, allowed method chaining with setters
- Made some other parameters nullable, added support for multiple file types (.svg, .png, .eps)
- Other minor code improvements

## 1.0.0 (2022-07-01)
### Added
- Initial library implementation
