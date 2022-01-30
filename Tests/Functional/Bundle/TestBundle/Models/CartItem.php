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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\Bundle\TestBundle\Models;

use Swagger\Annotations as SWG;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 *
 * @SWG\Definition(type="object", required={"vendorCode", "count", "price"})
 */
class CartItem
{
    /**
     * @var string
     *
     * @SWG\Property(pattern="^[0-9]{12}$")
     */
    public $vendorCode;

    /**
     * @var int
     *
     * @SWG\Property(
     *      default=1,
     *      maximum=10,
     *      exclusiveMaximum=false,
     *      minimum=0,
     *      exclusiveMinimum=true,
     *      multipleOf=1
     * )
     */
    public $count;

    /**
     * @var float
     *
     * @SWG\Property(
     *      default=0.1,
     *      minimum=0.1,
     *      exclusiveMinimum=false,
     * )
     */
    public $price;
}
