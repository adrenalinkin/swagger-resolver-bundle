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

use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 *
 * @SWG\Tag(name="password")
 */
class CustomerPasswordController
{
    /**
     * @Route(name="customers_password_create", path="/api/customers/{userId}/password", methods={"POST"})
     *
     * @SWG\Post(
     *      path="/api/customers/{userId}/password",
     *      description="Create new password when not even set",
     *      deprecated=true,
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
     *          name="userId",
     *          in="path",
     *          description="Customer ID to update",
     *          required=true,
     *          type="integer",
     *          format="int64",
     *          minimum=0,
     *          exclusiveMinimum=true,
     *      ),
     *      @SWG\Parameter(
     *          name="password",
     *          in="body",
     *          description="New password",
     *          required=true,
     *
     *          @SWG\Schema(type="string", maxLength=30),
     *      ),
     *
     *      @SWG\Response(response=204, description="Empty response when created successfully")
     * )
     */
    public function create(): void
    {
    }

    /**
     * @Route(name="customers_password_reset", path="/api/customers/{userId}/password", methods={"PUT"})
     *
     * @SWG\Put(
     *      path="/api/customers/{userId}/password",
     *      description="Reset password",
     *      deprecated=true,
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
     *          name="userId",
     *          in="path",
     *          description="Customer ID to update",
     *          required=true,
     *          type="integer",
     *          format="int64",
     *          minimum=0,
     *          exclusiveMinimum=true,
     *      ),
     *      @SWG\Parameter(
     *          name="password-reset",
     *          in="body",
     *          description="Body to change password",
     *          required=true,
     *
     *          @SWG\Schema(
     *              type="object",
     *              required={"oldPassword", "newPassword"},
     *
     *              @SWG\Property(
     *                  property="oldPassword",
     *                  type="string",
     *                  maxLength=30,
     *              ),
     *              @SWG\Property(
     *                  property="newPassword",
     *                  type="string",
     *                  maxLength=30,
     *              ),
     *          ),
     *      ),
     *
     *      @SWG\Response(response=204, description="Empty response when reset successfully")
     * )
     */
    public function reset(): void
    {
    }
}
