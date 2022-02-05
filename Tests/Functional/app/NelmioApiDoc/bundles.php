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

use Nelmio\ApiDocBundle\NelmioApiDocBundle;

$bundles = include __DIR__.'/../default/bundles.php';
$bundles[] = new NelmioApiDocBundle();

return $bundles;
