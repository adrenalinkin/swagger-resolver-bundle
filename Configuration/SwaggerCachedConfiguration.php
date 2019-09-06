<?php

declare(strict_types=1);

/*
 * This file is part of the SwaggerResolverBundle package.
 *
 * (c) Viktor Linkin <adrenalinkin@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Linkin\Bundle\SwaggerResolverBundle\Configuration;

use EXSyst\Component\Swagger\Path;
use EXSyst\Component\Swagger\Schema;
use Linkin\Bundle\SwaggerResolverBundle\Loader\SwaggerConfigurationLoaderInterface;
use Symfony\Component\Config\ConfigCacheFactory;
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use function json_decode;
use function json_encode;
use function md5;
use function sprintf;
use const PHP_SAPI;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class SwaggerCachedConfiguration extends SwaggerConfiguration implements WarmableInterface
{
    /**
     * @var string
     */
    private $cacheDir;

    /**
     * @var ConfigCacheFactoryInterface
     */
    private $configCacheFactory;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var SwaggerConfigurationLoaderInterface
     */
    private $loader;

    /**
     * @param SwaggerConfigurationLoaderInterface $loader
     * @param string $cacheDir
     * @param bool $debug
     */
    public function __construct(SwaggerConfigurationLoaderInterface $loader, string $cacheDir, bool $debug)
    {
        parent::__construct($loader);

        $this->cacheDir = $cacheDir . '/linkin_swagger_resolver';
        $this->debug = $debug;
        $this->loader = $loader;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefinition(string $definitionName): Schema
    {
        $cache = $this->getConfigCacheFactory()->cache(
            sprintf('%s/definitions/%s_%s.php', $this->cacheDir, $definitionName, md5($definitionName)),
            function (ConfigCacheInterface $cache) use ($definitionName) {
                $this->dumpDefinition($definitionName, $cache);
            }
        );

        return include $cache->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function getPathDefinition(string $routeName, string $method): Schema
    {
        $cache = $this->getConfigCacheFactory()->cache(
            sprintf('%s/paths/%s/%s_%s.php', $this->cacheDir, $routeName, $method, md5($routeName . $method)),
            function (ConfigCacheInterface $cache) use ($routeName, $method) {
                $this->dumpOperation($routeName, $method, $cache);
            }
        );

        return include $cache->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        $definitionWithoutResources = [];
        $definitionCollection = $this->loader->getSchemaDefinitionCollection();

        foreach ($definitionCollection->getIterator() as $definitionName => $definition) {
            $this->getDefinition($definitionName);

            if (empty($definitionCollection->getSchemaResources($definitionName))) {
                $definitionWithoutResources[$definitionName] = $definitionName;
            }
        }

        $operationCollection = $this->loader->getSchemaOperationCollection();

        /** @var Path $pathObject */
        foreach ($operationCollection as $routeName => $methodList) {
            foreach ($methodList as $method => $operation) {
                $this->getPathDefinition($routeName, $method);
            }

            if (empty($operationCollection->getSchemaResources($routeName))) {
                $definitionWithoutResources[$routeName] = $routeName;
            }
        }

        if ($definitionWithoutResources && PHP_SAPI === 'cli') {
            $this->displayConsoleNote(
                'LinkinSwaggerResolverBundle can\'t find source files for next definitions to auto-warm up cache:',
                true
            );

            foreach ($definitionWithoutResources as $definitionName) {
                $this->displayConsoleNote($definitionName, false);
            }

            echo "\n";
        }
    }

    /**
     * @param string $message
     * @param bool $firstLine
     */
    private function displayConsoleNote(string $message, bool $firstLine): void
    {
        $message = $firstLine ? sprintf('[NOTE] %s', $message) : sprintf('       %s', $message);
        $message = sprintf("\e[33m ! %s \e[39m\n", $message);

        echo $firstLine ? "\n" . $message : $message;
    }

    /**
     * @param string $definitionName
     * @param ConfigCacheInterface $cache
     */
    private function dumpDefinition(string $definitionName, ConfigCacheInterface $cache): void
    {
        $definition = parent::getDefinition($definitionName);

        $resources = $this->loader->getSchemaDefinitionCollection()->getSchemaResources($definitionName);

        $this->dumpSchema($definition, $resources, $cache);
    }

    /**
     * @param string $routeName
     * @param string $method
     * @param ConfigCacheInterface $cache
     */
    private function dumpOperation(string $routeName, string $method, ConfigCacheInterface $cache): void
    {
        $definition = parent::getPathDefinition($routeName, $method);

        $resources = $this->loader->getSchemaOperationCollection()->getSchemaResources($routeName);

        $this->dumpSchema($definition, $resources, $cache);
    }

    /**
     * @param Schema $schema
     * @param FileResource[] $resources
     * @param ConfigCacheInterface $cache
     */
    private function dumpSchema(Schema $schema, array $resources, ConfigCacheInterface $cache): void
    {
        $template = <<<EOF
<?php

declare(strict_types=1);

use EXSyst\Component\Swagger\Schema;

return new Schema(%s);

EOF;

        // to avoid problem with unexpected stdClass
        $definitionAsArray = json_decode(json_encode($schema->toArray()), true);
        $definitionExport = var_export($definitionAsArray, true);

        $cache->write(sprintf($template, $definitionExport), $resources);
    }

    /**
     * @return ConfigCacheFactoryInterface
     */
    private function getConfigCacheFactory(): ConfigCacheFactoryInterface
    {
        if (!$this->configCacheFactory) {
            $this->configCacheFactory = new ConfigCacheFactory($this->debug);
        }

        return $this->configCacheFactory;
    }
}
