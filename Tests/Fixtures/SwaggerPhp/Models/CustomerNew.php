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
 * @SWG\Definition(type="object", required={"name", "roles", "email", "password"})
 */
class CustomerNew
{
    /**
     * @var string
     *
     * @SWG\Property(minLength=2, maxLength=50)
     */
    public $name;

    /**
     * @var string
     *
     * @SWG\Property(minLength=2, maxLength=50)
     */
    public $secondName;

    /**
     * @var string[]
     *
     * @SWG\Property(
     *      uniqueItems=true,
     *      minItems=1,
     *      maxItems=2,
     *      @SWG\Items(
     *          type="string",
     *          enum={"guest", "user", "admin"},
     *      )
     * )
     */
    public $roles;

    /**
     * @var string
     *
     * @SWG\Property(maxLength=30)
     */
    public $password;

    /**
     * @var string
     *
     * @SWG\Property(pattern="^[0-9a-z]+\@crud\.com$")
     */
    public $email;

    /**
     * @var string
     *
     * @SWG\Property(format="date")
     */
    public $birthday;

    /**
     * @var string
     *
     * @SWG\Property(format="time", default="09:00")
     */
    public $happyHour;

    /**
     * @var int
     *
     * @SWG\Property(
     *      format="int32",
     *      default=0,
     *      multipleOf=10,
     *      minimum=0,
     *      exclusiveMinimum=false,
     *      maximum=100,
     *      exclusiveMaximum=true,
     * )
     */
    public $discount;
}
