## [0.2.0] - 2018-12-27
### Added
- Added possibility for creating `SwaggerResolver` object for all defined swagger request parameters.
- Added `SwaggerConfigurationLoaderInterface` into container as alias for the actual configuration loader service.
- Added possibility for use different strategies when performing resolving for the full request.
- Added new parameter `path_merge_strategy`.
- Added auto-configuration for the `SwaggerValidatorInterface`.
### Changed
- Renamed `services.yml` into `services.yaml`.
### Removed
- Removed compatibility with Symfony lower than 3.4.

## [0.1.3] - 2018-12-06
### Fixed
- Fixed problem with object type mapping from the documentation to allowed types in PHP.

## [0.1.2] - 2018-10-17
### Added
- Added correct processing of the objects references.

## [0.1.1] - 2018-08-28
### Fixed
- Fixed incorrect type hinting for the `number`.

## [0.1.0] - 2018-08-27
### Added
- First release of this bundle.
