Change log
==========

[2.0.2] - 2019-02-23
--------------------

### Added

- `Krizalys\Onedrive\Proxy\BaseItemVersionProxy`.
- `Krizalys\Onedrive\Proxy\DriveItemVersionProxy`.
- `Krizalys\Onedrive\Proxy\ItemReferenceProxy::path`.
- Some unit test cases.
- Some functional test cases.

### Changed

- `Krizalys\Onedrive\Proxy\DriveItem::versions`: returns an array of
`Krizalys\Onedrive\Proxy\DriveItemVersionProxy` instances.
- `Krizalys\Onedrive\Proxy\SharePointIdsProxy`: renamed into
`SharepointIdsProxy` to match its Microsoft Graph model.

### Fixed

- `Krizalys\Onedrive\Proxy\DriveItemProxy::createFolder()` was throwing an
exception when a folder with the same name already existed despite the conflict
behavior being set to `replace`.
- `Krizalys\Onedrive\Proxy\DriveItemProxy::upload()` was throwing an exception
when a file with the same name already existed despite the conflict behavior
being set to `replace`.
- `Krizalys\Onedrive\Proxy\BaseItemProxy::lastModifiedByUser` was causing a
fatal error.
- `Krizalys\Onedrive\Proxy\SystemFacetProxy::__construct()` was causing a fatal
error.

[2.0.1] - 2018-11-11
--------------------

### Added

- Some unit test cases.

### Fixed

- `Krizalys\Onedrive\Client::renewAccessToken()` was not setting its instance'
  token data as expected.
- `Krizalys\Onedrive\Proxy\DriveProxy::getRoot()` was sending a collection
  request instead of a normal request.

[2.0.0] - 2018-11-01
--------------------

### Added

- Support for logging, using [monolog/monolog][monolog] by default.
- Additional functional test cases.

### Removed

- Support for PHP 5.4 and PHP 5.5.
- Example application.
- `Krizalys\Onedrive\Folder::fetchDescendantDriveItems()`.
- `Krizalys\Onedrive\Client::apiDelete()`.
- `Krizalys\Onedrive\Client::apiPost()`.
- `Krizalys\Onedrive\Client::apiPut()`.
- `Krizalys\Onedrive\Client::apiMove()`.
- `Krizalys\Onedrive\Client::apiCopy()`.
- `Krizalys\Onedrive\Client::fetchPublicDocs`.
- `Krizalys\Onedrive\Client::fetchAccountInfo`.

### Changed

- License: GNU General Public License v3.0 => BSD 3-Clause License.
- `Krizalys\Onedrive\Client::createFolder()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::createFile()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::createFile()`: `$content` is automatically closed
  if it is a resource.
- `Krizalys\Onedrive\Client::fetchDriveItem()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchRoot()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchCameraRoll()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchDocs()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchPics()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchProperties()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchDriveItems()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::updateDriveItem()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::moveDriveItem()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::copyFile()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::deleteDriveItem()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchQuota()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchRecentDocs()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Client::fetchShared()`: deprecated & logs a warning.
- `Krizalys\Onedrive\DriveItem::move()`: deprecated & logs a warning.
- `Krizalys\Onedrive\File::fetchContent()`: deprecated & logs a warning.
- `Krizalys\Onedrive\File::copy()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Folder::fetchDriveItems()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Folder::fetchChildDriveItems()`: deprecated & logs a
  warning.
- `Krizalys\Onedrive\Folder::createFolder()`: deprecated & logs a warning.
- `Krizalys\Onedrive\Folder::createFile()`: deprecated & logs a warning.

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

[unreleased]: https://github.com/krizalys/onedrive-php-sdk/compare/2.0.2...HEAD
[2.0.2]:      https://github.com/krizalys/onedrive-php-sdk/compare/2.0.1...2.0.2
[2.0.1]:      https://github.com/krizalys/onedrive-php-sdk/compare/2.0.0...2.0.1
[2.0.0]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.2.0...2.0.0
[1.2.0]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.1.1...1.2.0
[1.1.1]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.1.0...1.1.1
[1.1.0]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.0.0...1.1.0
[1.0.1]:      https://github.com/krizalys/onedrive-php-sdk/compare/1.0.0...1.0.1
[monolog]:    https://github.com/seldaek/monolog
