Change log
==========

[Unreleased][unreleased]
------------------------

### Added

- Support for logging, using [monolog/monolog][monolog] by default.
- Additional functional test cases.

### Removed

- Support for PHP 5.4 and PHP 5.5.
- Example application.
- `Krizalys\Onedrive\Folder::fetchDescendantDriveItems()`.

### Changed

- License changed from GNU General Public License v3.0 to BSD 3-Clause License.

[1.2.0] - 2017-12-09
--------------------

### Added

- Support for PHP 7.1.
- Support for PHP 7.2.
- Functional test suite.

[1.1.1] - 2017-03-26
--------------------

### Fixed

- Support for SSL.

[1.1.0] - 2016-07-10
--------------------

### Added

- Support for refresh tokens.
- Support for multiple naming conflict behaviors when uploading files.
- Support for multiple PHP stream back ends when uploading files.
- Standalone autoloader.
- Unit test suite & code coverage.

### Removed

- Support for PHP 5.3.

[1.0.1] - 2017-03-26
--------------------

### Fixed

- Support for SSL.

[unreleased]: https://github.com/krizalys/onedrive-php-sdk/compare/1.2.0...HEAD
[1.2.0]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.1.1...1.2.0
[1.1.1]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.1.0...1.1.1
[1.1.0]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.0.0...1.1.0
[1.0.1]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.0.0...1.0.1
[monolog]:    https://github.com/seldaek/monolog
