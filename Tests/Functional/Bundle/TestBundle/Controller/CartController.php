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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\Bundle\TestBundle\Controller;

use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\SwaggerPhp\Models\Cart;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\SwaggerPhp\Models\CartItem;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Fixtures\SwaggerPhp\Models\ResponseCreated;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 *
 * @SWG\Tag(name="cart")
 */
class CartController
{
    /**
     * Add new item into cart or increase count of existed.
     *
     * @Route(name="cart_add_item", path="/cart", methods={"PUT"})
     *
     * @SWG\Parameter(
     *     name="x-auth-token",
     *     in="header",
     *     description="Alternative token for the authorization",
     *     required=true,
     *     type="string",
     *     pattern="^\w{36}$",
     * )
     * @SWG\Parameter(
     *     name="cart",
     *     in="body",
     *     description="Item data to add to the cart",
     *     required=true,
     *     @Model(type=CartItem::class),
     * )
     * @SWG\Response(
     *     response=201,
     *     description="New item into cart ID",
     *     @Model(type=ResponseCreated::class),
     * )
     */
    public function addItem(): Response
    {
        return new Response(Response::HTTP_CREATED);
    }

    /**
     * Returns all items from the cart.
     *
     * @Route(name="cart_get", path="/cart", methods={"GET"})
     *
     * @SWG\Parameter(
     *     name="x-auth-token",
     *     in="header",
     *     description="Alternative token for the authorization",
     *     required=true,
     *     type="string",
     *     pattern="^\w{36}$",
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Cart data",
     *     @Model(type=Cart::class),
     * )
     */
    public function getCartData(): Response
    {
        return new Response();
    }
}
