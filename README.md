Swagger Resolver Bundle [![На Русском](https://img.shields.io/badge/Перейти_на-Русский-green.svg?style=flat-square)](./README.RU.md)
=======================

[![Latest Stable Version](https://poser.pugx.org/adrenalinkin/swagger-resolver-bundle/v/stable)](https://packagist.org/packages/adrenalinkin/swagger-resolver-bundle)
[![Total Downloads](https://poser.pugx.org/adrenalinkin/swagger-resolver-bundle/downloads)](https://packagist.org/packages/adrenalinkin/swagger-resolver-bundle)

Feel free to connect with me by email [email](mailto:adrenalinkin@gmail.com)
or in Telegram [Telegram](https://t.me/adrenaL1nkin).

Usage example on [GitHub Gist](https://gist.github.com/adrenalinkin/f5cddf1afea865a3b91ac078a1cb8337#file-instruction-md)

Introduction
------------

Bundle provides possibility for validate data according to the Swagger 2 documentation.
You describe your API documentation by Swagger and provides verification of data for compliance
with the described requirements.
When documentation has been updated then verification will be updated too, all in one place!

**Documentation is cached** through the standard `Symfony Warmers` mechanism.
In debug mode, the cache automatically warms up if you change the file containing the description of the documentation.

*Note:* as result bundle returns `SwaggerResolver` object - extension for the
[OptionsResolver](https://github.com/symfony/options-resolver). 
In this way you get full control over created resolver. 

*Attention:* remember, when you change generated `SwaggerResolver` object you risk to get 
divergence with actual documentation.

### Integrations

Bundle provides integration with [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle),
supports configuration loading by [swagger-php](https://github.com/zircote/swagger-php) and also supports
loading directly from the `json` or `yaml`(`yml`) configuration file.
When used default bundle configuration then swagger documentation will be load in most optimal available way.
Loaders priority: 
1. `NelmioApiDocBundle` - do not require any additional configuration.
2. `swagger-php` - scan `src/` directory by default. Uses `swagger_php.scan` and `swagger_php.exclude` parameters.
3. `json` - looking for `web/swagger.json` by default. Uses `configuration_file` parameter.

Installation
-----------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download
the latest stable version of this bundle:
```bash
    composer require adrenalinkin/swagger-resolver-bundle
```
*is command requires you to have [Composer](https://getcomposer.org) install globally.*

### Step 2: Enable the Bundle

Then, enable the bundle by updating your `app/AppKernel.php` file to enable the bundle:

```php
<?php declare(strict_types=1);
// app/AppKernel.php

class AppKernel extends Kernel
{
    // ...

    public function registerBundles()
    {
        $bundles = [
            // ...

            new Linkin\Bundle\SwaggerResolverBundle\LinkinSwaggerResolverBundle(),
        ];

        return $bundles;
    }

    // ...
}
```

Configuration
------------

To start using bundle you don't need to define some additional configuration.
All parameters has values by default:

```yaml
# app/config.yml
linkin_swagger_resolver:
    # default parameter locations which can apply normalization
    enable_normalization:
        - 'query'
        - 'path'
        - 'header'
    # strategy for merge all request parameters.
    path_merge_strategy:            Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\StrictMergeStrategy
    configuration_loader_service:   ~   # the name of the configuration loader service
    configuration_file:             ~   # full path to the configuration file
    swagger_php:                        # settings for the swagger-php
        scan:       ~                   # array of the full paths for the annotations scan
        exclude:    ~                   # array of the full paths which should be excluded
```

Usage
-----

### Step 1: Swagger documentation preparation

Swagger documentation preparation differ according to used tools of your project.

**NelmioApiDocBundle** 

If your project has `NelmioApiDocBundle` connected, then no additional configuration is required.

**swagger-php** 

In the absence of `NelmioApiDocBundle`, the bundle will degrades to the configuration
loading by `swagger-php` annotations. In this case, by default, will be used `src/` directory to scan.
To optimize scanning process you can describe directories in the configuration:

```yaml
# app/config.yml
linkin_swagger_resolver:
    swagger_php:
        scan:
            - '%kernel.project_dir%/src/Acme/ApiBundle'
        exclude:
            - '%kernel.project_dir%/src/Acme/ApiBundle/Resources'
            - '%kernel.project_dir%/src/Acme/ApiBundle/Repository'
```

**JSON** 

In the absence of `NelmioApiDocBundle` and `swagger-php`, the bundle will degrades to the configuration
loading by `json` file. In this case, by default, will be used `web/swagger.json` file.
Also you can set custom path to the `json` configuration:

```yaml
# app/config.yml
linkin_swagger_resolver:
    configuration_file: '%kernel.project_dir%/web/swagger.json' # json extension is required
```

**YAML** or *(yml)* 

In the absence of `NelmioApiDocBundle` and `swagger-php`, but available
configuration file in the `yaml` or `yml` format you need to define path to that file:  

```yaml
# app/config.yml
linkin_swagger_resolver:
    configuration_file: '%kernel.project_dir%/web/swagger.yaml' # yaml or yml extension is required
```

**Custom**

If you need to use custom configuration loader you should implement custom loading process in the class, which
implements [SwaggerConfigurationLoaderInterface](./Loader/SwaggerConfigurationLoaderInterface.php) interface.
After that you need to define name of that service in the configuration: 

```yaml
# app/config.yml
linkin_swagger_resolver:
    configuration_loader_service: acme_app.custom_configuration_loader
```

### Step 2: Data validation

#### Validation for model

```php
<?php declare(strict_types=1);

/** @var \Linkin\Bundle\SwaggerResolverBundle\Factory\SwaggerResolverFactory $factory */
$factory = $container->get('linkin_swagger_resolver.factory');
// loading by fully qualified class name
$swaggerResolver = $factory->createForDefinition(AcmeApiModel::class);
// loading by class name
$swaggerResolver = $factory->createForDefinition('AcmeApiModel');

/** @var \Symfony\Component\HttpFoundation\Request $request */
$data = $swaggerResolver->resolve(json_decode($request->getContent(), true));
```

#### Validation for request

```php
<?php declare(strict_types=1);

/** @var \Linkin\Bundle\SwaggerResolverBundle\Factory\SwaggerResolverFactory $factory */
$factory = $container->get('linkin_swagger_resolver.factory');
$request = $container->get('request_stack')->getCurrentRequest();
// Loading by request
$swaggerResolver = $factory->createForRequest($request);

$data = $swaggerResolver->resolve(json_decode($request->getContent(), true));
```

Advanced
--------

### Custom validator

Bundle validates the data through the validator system.
List of all validators, you can find out by going to the [Validator](./Validator) folder.
All validators registered as tagging services. To create your own validator it is enough to create class,
which implements [SwaggerValidatorInterface](./Validator/SwaggerValidatorInterface.php) and then
register it under the tag `linkin_swagger_resolver.validator`.

### Custom normalizer

Bundle validates the data through the normalizer system.
List of all normalizers, you can find out by going to the [Normalizer](./Normalizer) folder.
All normalizers registered as tagging services. To create your own normalizer it is enough to create class,
which implements [SwaggerNormalizerInterface](./Normalizer/SwaggerNormalizerInterface.php) and then
register it under the tag `linkin_swagger_resolver.normalizer`.

License
-------

[![license](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](./LICENSE)
