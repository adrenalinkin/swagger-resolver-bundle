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
 * @SWG\Definition(type="object", required={"id", "name", "roles", "email", "registeredAt"})
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
     * @var array
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
     *      multipleOf=10,
     *      minimum=0,
     *      exclusiveMinimum=false,
     *      maximum=100,
     *      exclusiveMaximum=true,
     * )
     */
    public $discount;

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
