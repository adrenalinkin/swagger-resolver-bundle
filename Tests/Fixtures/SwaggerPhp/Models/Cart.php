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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\SwaggerPhp\Models;

use Swagger\Annotations as SWG;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 *
 * @SWG\Definition(type="object", required={"totalPrice", "itemList"})
 */
class Cart
{
    /**
     * @var float
     *
     * @SWG\Property(default=0.1, minimum=0.1, exclusiveMinimum=false)
     */
    public $totalPrice;

    /**
     * @var array
     *
     * @SWG\Property(
     *      minItems=0,
     *      maxItems=5,
     *      @SWG\Items(ref="#/definitions/CartItem")
     * )
     */
    public $itemList;

    /**
     * @var CartItem
     *
     * @SWG\Property(ref="#/definitions/CartItem")
     */
    public $lastAddedItem;

    /**
     * @var array
     *
     * @SWG\Property(
     *      required={"code", "captcha"},
     *      type="object",
     *      @SWG\Property(
     *          property="code",
     *          type="string",
     *          maxLength=15,
     *          minLength=5,
     *      ),
     *      @SWG\Property(
     *          property="captcha",
     *          type="string",
     *          maxLength=7,
     *          minLength=7,
     *      ),
     * )
     */
    public $promo;
}
