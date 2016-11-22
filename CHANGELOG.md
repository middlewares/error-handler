# Change Log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) 
and this project adheres to [Semantic Versioning](http://semver.org/).

## 0.3.0 - 2016-11-22

### Changed

* Updated to `http-interop/http-middleware#0.3`

## 0.2.0 - 2016-11-19

### Changed

* Changed the handler signature to `function(ServerRequestInterface $request)`.
* The error info is passed to the handler using an array stored in the request attribute `error`.

### Added

* New option `attribute()` to change the attribute name used to pass the error info to the handler.

## 0.1.0 - 2016-10-03

First version
