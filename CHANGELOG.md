## [1.0.0] - 2023-02-05
### Added
- Added php 8 support (#82)
### Changed
- Changed `datetime` format to `date-time` according to [swagger specification](https://swagger.io/specification/) (#60)
- Applied `time`, `date`, `date-time` format validation
  according to specification [RFC3339](https://xml2rfc.tools.ietf.org/public/rfc/html/rfc3339.html#anchor14) (#60)
- Improved resource registration for the `SwaggerPhp` by avoid `get_declared_classes` usage (#63)
### Removed
- Removed all unnecessary rows from the `.gitignore` according to `.gitignore_global` use instead (#25)
### Fixed
- Fixed incompatible with Symfony 5 according to new contract in `OptionResolver::offsetGet()` (#58)
- Added conflict with `nelmio/api-doc-bundle` version lower than `3.4` (#70)
- Fixed error when `SwaggerResolver` without any validator (#58)
- Fixed incorrect behavior of the `multipleOf` validation when received value and `multipleOf` was a float type (#49)
- Fixed not worked array validation for the `multi` format (#56)
- Fixed incorrect behaviour of the `ReplaceLastWinMergeStrategy` when first duplicate item was marked as required
  and second item marked as not required (#62)
- Fixed working with collection by avoid resource duplication (#63)
- Fixed incorrect behaviour for the `swagger_php` configuration (#74)
- Fixed bug with compiler pass priority for normalizers and validators (#77)
- Fixed bug when default area should be always define in `NelmioApiDocBundle` (#77)

## [0.4.6] - 2020-04-01
### Fixed
- Fixed problem with deprecated DI configuration (#20)

## [0.4.5] - 2020-03-16
### Changed
- Symfony 5 allowed (#19)

## [0.4.4] - 2019-11-07
### Fixed
- Fixed problem with case-sensitive request method (#16)

## [0.4.3] - 2019-09-23
### Removed
- Removed `PathNotFoundException` as redundant (#14)
### Fixed
- Fixed problem with path to route name when http method has been ignored (#14)

## [0.4.2] - 2019-09-09
### Changed
- Improved performance due to avoid of usage `RouterInterface::getRouteCollection` at runtime (#11)

## [0.4.1] - 2019-04-13
### Added
- Added support of the several areas when `NelmioApiDocBundle` used for the configuration loading (#9)

## [0.4.0] - 2019-03-24
### Added
- Added symfony cache warmer for the swagger configuration and enable him by default (#6)
- Added console notification when some api definitions have not referenced to the source file (#6)
- Added automatic cache warm up in the debug mode, according to source file modification (#6)
- Added composer requirement: `symfony/yaml` (#6)
### Changed
- Extend `NormalizationFailedException` from `InvalidOptionsException` instead `RuntimeException` (#6)
- Removed possibility set `MergeStrategyInterface` for single call `SwaggerResolverFactory::createForRequest` (#6)
- Renamed `PathParameterMerger` into `OperationParameterMerger` (#6)
- Reworked `SwaggerConfigurationLoaderInterface` (#6)

## [0.3.0] - 2019-03-03
### Added
- Added normalizers usage and provides possibility for enable this for concrete parameter locations.
- Added new configuration parameter `enable_normalization`.
- Added `SwaggerNormalizerInterface` and implementation for `integer`, `number` and `boolean`.
- Added enums for typical swagger parameter options:
    `ParameterCollectionFormatEnum`, `ParameterLocationEnum`, `ParameterTypeEnum`.
### Removed
- Removed `linkin_swagger_resolver.builder` alias.

## [0.2.0] - 2018-12-27
### Added
- Added possibility for creating `SwaggerResolver` object for all defined swagger request parameters.
- Added `SwaggerConfigurationLoaderInterface` into container as alias for the actual configuration loader service.
- Added possibility for use different strategies when performing resolving for the full request.
- Added new configuration parameter `path_merge_strategy`.
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
