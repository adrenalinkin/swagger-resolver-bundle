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
 * @SWG\Definition(type="object", required={"id", "name", "roles", "email", "isEmailConfirmed", "registeredAt"})
 */
class CustomerFull
{
    /**
     * @var int
     *
     * @SWG\Property(format="int64", minimum=0, exclusiveMinimum=true)
     */
    public $id;

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
     * @SWG\Property(pattern="^[0-9a-z]+\@crud\.com$")
     */
    public $email;

    /**
     * @var bool
     *
     * @SWG\Property()
     */
    public $isEmailConfirmed;

    /**
     * @var string
     *
     * @SWG\Property(format="date")
     */
    public $birthday;

    /**
     * @var string
     *
     * @SWG\Property(format="time")
     */
    public $happyHour;

    /**
     * @var int
     *
     * @SWG\Property(
     *      format="int32",
     *      default=0,
     *      maximum=100,
     *      exclusiveMaximum=true,
     *      minimum=0,
     *      exclusiveMinimum=false,
     *      multipleOf=10,
     * )
     */
    public $discount;

    /**
     * @var float
     *
     * @SWG\Property(
     *      default=0.1,
     *      maximum=5.1,
     *      exclusiveMaximum=true,
     *      minimum=0.1,
     *      exclusiveMinimum=false,
     * )
     */
    public $rating;

    /**
     * @var string
     *
     * @SWG\Property(format="date-time")
     */
    public $registeredAt;

    /**
     * @var string
     *
     * @SWG\Property(format="timestamp")
     */
    public $lastVisitedAt;
}
