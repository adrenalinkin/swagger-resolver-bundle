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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Response;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class AbstractCustomerPasswordController
{
    public function create(): Response
    {
        return new Response();
    }

    public function reset(): Response
    {
        return new Response();
    }
}
