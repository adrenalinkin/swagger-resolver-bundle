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
use Symfony\Component\Config\ConfigCacheFactoryInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\Config\ResourceCheckerConfigCacheFactory;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use function json_decode;
use function json_encode;
use function md5;
use function sprintf;

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
     * @required
     *
     * @param string $cacheDir
     */
    public function setCacheDir(string $cacheDir): void
    {
        $this->cacheDir = $cacheDir . '/linkin_swagger_resolver';
    }

    /**
     * @required
     *
     * @param bool $debug
     */
    public function setDebugMode(bool $debug): void
    {
        $this->debug = $debug;
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
    public function getPathDefinition(string $routePath, string $method): Schema
    {
        $cache = $this->getConfigCacheFactory()->cache(
            sprintf('%s/paths/%s/%s_%s.php', $this->cacheDir, $routePath, $method, md5($routePath . $method)),
            function (ConfigCacheInterface $cache) use ($routePath, $method) {
                $this->dumpOperation($routePath, $method, $cache);
            }
        );

        return include $cache->getPath();
    }

    /**
     * {@inheritdoc}
     */
    public function warmUp($cacheDir)
    {
        foreach ($this->getSchemaDefinitionList() as $definitionName => $definition) {
            $this->getDefinition($definitionName);
        }

        /** @var Path $pathObject */
        foreach ($this->getSchemaOperationList() as $path => $methodList) {
            foreach ($methodList as $method => $operation) {
                $this->getPathDefinition($path, $method);
            }
        }
    }

    /**
     * @param string $definitionName
     * @param ConfigCacheInterface $cache
     */
    private function dumpDefinition(string $definitionName, ConfigCacheInterface $cache): void
    {
        $definition = parent::getDefinition($definitionName);

        $this->dumpSchema($definition, $cache);
    }

    /**
     * @param string $path
     * @param string $method
     * @param ConfigCacheInterface $cache
     */
    private function dumpOperation(string $path, string $method, ConfigCacheInterface $cache): void
    {
        $definition = parent::getPathDefinition($path, $method);

        $this->dumpSchema($definition, $cache);
    }

    /**
     * @param Schema $schema
     * @param ConfigCacheInterface $cache
     */
    private function dumpSchema(Schema $schema, ConfigCacheInterface $cache): void
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

        // TODO: Add meta for auto-check is cache fresh in debug mode
        $cache->write(sprintf($template, $definitionExport));
    }

    /**
     * @return ConfigCacheFactoryInterface
     */
    private function getConfigCacheFactory(): ConfigCacheFactoryInterface
    {
        if (!$this->configCacheFactory) {
            // TODO: use ConfigCacheFactory when meta info will be implemented
            $this->configCacheFactory = new ResourceCheckerConfigCacheFactory();
        }

        return $this->configCacheFactory;
    }
}
