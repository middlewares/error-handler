# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## [0.6.0] - 2017-03-26

### Added

* Added `Middlewares\HttpErrorException` class to allow to pass data context to the error handler

## [0.5.0] - 2017-02-05

### Changed

* Updated to `middlewares/utils#~0.9

## [0.4.0] - 2016-12-26

### Changed

* Updated tests
* Updated to `http-interop/http-middleware#0.4`
* Updated `friendsofphp/php-cs-fixer#2.0`

## [0.3.0] - 2016-11-22

### Changed

* Updated to `http-interop/http-middleware#0.3`

## [0.2.0] - 2016-11-19

### Changed

* Changed the handler signature to `function(ServerRequestInterface $request)`.
* The error info is passed to the handler using an array stored in the request attribute `error`.

### Added

* New option `attribute()` to change the attribute name used to pass the error info to the handler.

## 0.1.0 - 2016-10-03

First version

[0.6.0]: https://github.com/middlewares/error-handler/compare/v0.5.0...v0.6.0
[0.5.0]: https://github.com/middlewares/error-handler/compare/v0.4.0...v0.5.0
[0.4.0]: https://github.com/middlewares/error-handler/compare/v0.3.0...v0.4.0
[0.3.0]: https://github.com/middlewares/error-handler/compare/v0.2.0...v0.3.0
[0.2.0]: https://github.com/middlewares/error-handler/compare/v0.1.0...v0.2.0
