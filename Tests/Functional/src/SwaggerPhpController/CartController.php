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

namespace Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\SwaggerPhpController;

use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\AbstractCartController;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 *
 * @SWG\Tag(name="cart")
 */
class CartController extends AbstractCartController
{
    /**
     * @Route(name="cart_add_item", path="/api/cart", methods={"PUT"})
     *
     * @SWG\Put(
     *      path="/api/cart",
     *      description="Add new item into cart or increase count of existed",
     *
     *      @SWG\Parameter(
     *          name="x-auth-token",
     *          in="header",
     *          description="Alternative token for the authorization",
     *          required=true,
     *          type="string",
     *          pattern="^\w{36}$",
     *      ),
     *      @SWG\Parameter(
     *          name="cart",
     *          in="body",
     *          description="Item data to add to the cart",
     *          required=true,
     *
     *          @SWG\Schema(ref="#/definitions/CartItem"),
     *      ),
     *
     *      @SWG\Response(
     *          response=201,
     *          description="New item into cart ID",
     *
     *          @SWG\Schema(ref="#/definitions/ResponseCreated"),
     *      )
     * )
     */
    public function addItem(): Response
    {
        return parent::addItem();
    }

    /**
     * @Route(name="cart_get", path="/api/cart", methods={"GET"})
     *
     * @SWG\Get(
     *      path="/api/cart",
     *      description="Returns all items from the cart",
     *
     *      @SWG\Parameter(
     *          name="x-auth-token",
     *          in="header",
     *          description="Alternative token for the authorization",
     *          required=true,
     *          type="string",
     *          pattern="^\w{36}$",
     *      ),
     *
     *      @SWG\Response(
     *          response=200,
     *          description="Cart data",
     *
     *          @SWG\Schema(ref="#/definitions/Cart"),
     *      )
     * )
     */
    public function getCartData(): Response
    {
        return parent::getCartData();
    }
}
