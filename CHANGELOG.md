# Imager Pretransform Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## Unreleased

### Added
- Added config option to disable the plugin
- Added Imager as a dependency

### Fixed
- Propagating assets or assets in draft state will now be skipped
- Fixed error when you mix multiple volume handles and global transforms 
- Fixed error when non-existing volume handle was passed to console command ([#19](https://github.com/superbigco/craft-imagerpretransform/issues/19))

## 2.0.2 - 2018-10-22
### Fixed
- Fixed subfolders support in console command

## 2.0.1 - 2018-10-22
### Added
- Added template transforms

## 2.0.0 - 2018-10-22
### Added
- Initial release
