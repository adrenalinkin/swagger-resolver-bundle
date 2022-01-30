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

use Linkin\Bundle\SwaggerResolverBundle\LinkinSwaggerResolverBundle;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\Bundle\TestBundle\TestBundle;
use Nelmio\ApiDocBundle\NelmioApiDocBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;

return [
    new FrameworkBundle(),
    new NelmioApiDocBundle(),
    new LinkinSwaggerResolverBundle(),
    new TestBundle(),
];
