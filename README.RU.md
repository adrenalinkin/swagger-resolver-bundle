Swagger Resolver Bundle [![In English](https://img.shields.io/badge/Switch_To-English-green.svg?style=flat-square)](./README.md)
=======================

[![PHPUnit](https://github.com/adrenalinkin/swagger-resolver-bundle/workflows/UnitTests/badge.svg)](https://github.com/adrenalinkin/swagger-resolver-bundle/actions/workflows/unit-tests.yml)
[![Coverage Status](https://coveralls.io/repos/github/adrenalinkin/swagger-resolver-bundle/badge.svg?branch=master)](https://coveralls.io/github/adrenalinkin/swagger-resolver-bundle?branch=master)
[![Latest Stable Version](https://poser.pugx.org/adrenalinkin/swagger-resolver-bundle/v/stable)](https://packagist.org/packages/adrenalinkin/swagger-resolver-bundle)
[![Total Downloads](https://poser.pugx.org/adrenalinkin/swagger-resolver-bundle/downloads)](https://packagist.org/packages/adrenalinkin/swagger-resolver-bundle)

При возникновении вопросов можно связаться по [email](mailto:adrenalinkin@gmail.com)
или через [Telegram](https://t.me/adrenaL1nkin).

Пример использования на [GitHub Gist](https://gist.github.com/adrenalinkin/f5cddf1afea865a3b91ac078a1cb8337#file-instruction-md)

Введение
--------

Бандл предоставляет возможность валидировать данные в соответствии с описанной документацией Swagger 2.
Единожды описав документацию api при помощи swagger вы получаете проверку данных на соответствие описанным требованиям.
Обновляется документации - обновляются требования, все в одном месте!

**Документация кэшируется** посредством стандартного механизма `Symfony Warmers`.
В режиме отладки кэш автоматически прогревается если изменить файл, содержащий описание документации.

*Примечание:* в качестве ответа приходит объект `SwaggerResolver` расширение для
[OptionsResolver](https://github.com/symfony/options-resolver). Таким образом вы получаете полный контроль
над созданным набором требований к данным.

*Внимание:* помните что внося изменения в предустановленный набор требований к данным
вы рискуете получить расхождение с актуальной документацией.

### Интеграции

Бандл предоставляет автоматическую интеграцию с [NelmioApiDocBundle](https://github.com/nelmio/NelmioApiDocBundle),
поддерживает загрузку конфигурации из [swagger-php](https://github.com/zircote/swagger-php), а также загрузку
конфигурации непосредственно из файла `json` или `yaml`(`yml`).
При отсутствии дополнительной конфигурации бандл автоматически подключит самый оптимальный доступный способ загрузки
конфигурации. Порядок приоритета:
1. `NelmioApiDocBundle` - не требует дополнительной конфигурации.
2. `swagger-php` - По умолчанию сканирует папку `src/`. Использует параметры `swagger_php.scan` и`swagger_php.exclude`.
3. `json` - По умолчанию ищет файл `web/swagger.json`. Использует параметр `configuration_file`.

Установка
---------

### Шаг 1: Загрузка бандла

Откройте консоль и, перейдя в директорию проекта, выполните следующую команду для загрузки наиболее подходящей
стабильной версии этого бандла:
```bash
    composer require adrenalinkin/swagger-resolver-bundle
```
*Эта команда подразумевает что [Composer](https://getcomposer.org) установлен и доступен глобально.*

### Шаг 2: Подключение бандла

После включите бандл добавив его в список зарегистрированных бандлов в `app/AppKernel.php` файл вашего проекта:

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

Конфигурация
------------

Для использования бандла не требуется предварительной конфигурации.
Все параметры имеют значения по умолчанию:

```yaml
# app/config.yml
linkin_swagger_resolver:
    # список локаций параметров по умолчания, для которых включена нормализация
    enable_normalization:
        - 'query'
        - 'path'
        - 'header'
    # стратегия для слияния параметров запроса
    path_merge_strategy:            Linkin\Bundle\SwaggerResolverBundle\Merger\Strategy\StrictMergeStrategy
    configuration_loader_service:   ~   # имя сервиса загрузки конфигурации
    configuration_file:             ~   # полный путь к файлу конфигурации
    swagger_php:                        # настройки для swagger-php
        scan:       ~                   # массив полных путей для сканирования аннотаций
        exclude:    ~                   # массив полных путей которые стоит исключить
```

Использование
-------------

### Шаг 1: Подготовка swagger документации

Подготовка swagger документации отличается в зависимости от используемых инструментов в вашем проекте.

**NelmioApiDocBundle**

Если в вашем проекте подключен `NelmioApiDocBundle` то дополнительная конфигурация не требуется.

**swagger-php**

В случае отсутствия `NelmioApiDocBundle` бандл деградирует до загрузки конфигурации
на основании аннотаций `swagger-php`. При этом для сканирования будет использована директория проекта `src/`.
Чтобы оптимизировать сканирование, вы можете указать директории явно:

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

В случае отсутствия `NelmioApiDocBundle` и `swagger-php` бандл деградирует до загрузки конфигурации
из `json` файла. По умолчанию происходит поиск файла `web/swagger.json`.
Вы также можете указать путь к файлу с конфигурацией:

```yaml
# app/config.yml
linkin_swagger_resolver:
    configuration_file: '%kernel.project_dir%/web/swagger.json' # обязательно расширение файла json
```

**YAML** or *(yml)*

В случае отсутствия `NelmioApiDocBundle` и `swagger-php` и наличия конфигурации в формате `yaml` (`yml`)
вам необходимо указать полный путь к файлу в конфигурации бандла:

```yaml
# app/config.yml
linkin_swagger_resolver:
    configuration_file: '%kernel.project_dir%/web/swagger.yaml' # обязательно расширение файла yaml или yml
```

**Custom**

При необходимости использовать собственный загрузчик вам необходимо реализовать требуемое поведение в классе,
реализующем интерфейс [SwaggerConfigurationLoaderInterface](./Loader/SwaggerConfigurationLoaderInterface.php).
После чего необходимо указать название сервиса этого класса в конфигурации:

```yaml
# app/config.yml
linkin_swagger_resolver:
    configuration_loader_service: acme_app.custom_configuration_loader
```

### Шаг 2: Валидация данных

#### Валидация модели

```php
<?php declare(strict_types=1);

/** @var \Linkin\Bundle\SwaggerResolverBundle\Factory\SwaggerResolverFactory $factory */
$factory = $container->get('linkin_swagger_resolver.factory');
// загрузка по полному имени класса модели
$swaggerResolver = $factory->createForDefinition(AcmeApiModel::class);
// загрузка имени класса модели
$swaggerResolver = $factory->createForDefinition('AcmeApiModel');

/** @var \Symfony\Component\HttpFoundation\Request $request */
$data = $swaggerResolver->resolve(json_decode($request->getContent(), true));
```

#### Валидация всего запроса

```php
<?php declare(strict_types=1);

/** @var \Linkin\Bundle\SwaggerResolverBundle\Factory\SwaggerResolverFactory $factory */
$factory = $container->get('linkin_swagger_resolver.factory');
$request = $container->get('request_stack')->getCurrentRequest();
// загрузка всех параметров вызванного метода запроса
$swaggerResolver = $factory->createForRequest($request);

$data = $swaggerResolver->resolve(json_decode($request->getContent(), true));
```

Дополнительно
-------------

### Собственный валидатор

Бандл производит валидацию данных посредством системы валидаторов.
Со списком всех валидаторов вы можете ознакомиться перейдя в папку [Validator](./Validator).
Валидаторы являются тегированными сервисами. Чтобы создать свой собственный валидатор, достаточно создать
класс, реализующий интерфейс [SwaggerValidatorInterface](./Validator/SwaggerValidatorInterface.php) и
зарегистрировать его с тегом `linkin_swagger_resolver.validator`.

### Собственный нормализатор

Бандл производит нормализацию данных посредством системы нормализаторов.
Со списком всех нормализаторов вы можете ознакомиться перейдя в папку [Normalizer](./Normalizer).
Нормализаторы являются тегированными сервисами. Чтобы создать свой собственный нормализатор, достаточно создать
класс, реализующий интерфейс [SwaggerNormalizerInterface](./Normalizer/SwaggerNormalizerInterface.php) и
зарегистрировать его с тегом `linkin_swagger_resolver.normalizer`.

### Запуск тестов и инструментов статического анализа
Скачать проект:
```bash
git clone git@github.com:adrenalinkin/swagger-resolver-bundle.git
cd swagger-resolver-bundle
```

Пройти [по инструкции](https://gist.github.com/adrenalinkin/35176a7b52b996666c4a36384fd536ad#file-an-instruction-md)
для настройки простого docker контейнера.

Установить Composer зависимости:
```bash
composer update
```

Для запуска тестов выполнить:
```bash
# все тесты
bin/simple-phpunit
# только Unit
bin/simple-phpunit --testsuite=unit
# только Functional используется loader SwaggerPhp
bin/simple-phpunit --testsuite=functional
# только Functional используется loader NelmioApiDoc
FORCE_LOADER=NelmioApiDoc bin/simple-phpunit --testsuite=functional
# только Functional используется loader FileAppKernel
FORCE_LOADER=FileAppKernel bin/simple-phpunit --testsuite=functional
```

Для запуска [PHP-CS-Fixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) выполнить:
```bash
php-cs-fixer fix --diff
```

Для запуска [composer-normalize](https://github.com/ergebnis/composer-normalize) выполнить:
```bash
composer-normalize --dry-run
```

Лицензия
--------

[![license](https://img.shields.io/badge/License-MIT-green.svg?style=flat-square)](./LICENSE)
