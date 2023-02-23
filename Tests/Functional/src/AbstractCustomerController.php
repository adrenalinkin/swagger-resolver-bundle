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

use Linkin\Bundle\SwaggerResolverBundle\Factory\SwaggerResolverFactory;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\Models\CustomerFull;
use Linkin\Bundle\SwaggerResolverBundle\Tests\Functional\Models\ResponseCreated;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Viktor Linkin <adrenalinkin@gmail.com>
 */
class AbstractCustomerController
{
    public function getAll(Request $request, SwaggerResolverFactory $factory): JsonResponse
    {
        $requestResolver = $factory->createForRequest($request);
        $requestResolver->resolve([
            'page' => $request->query->getInt('page'),
            'perPage' => $request->query->getInt('perPage'),
            'x-auth-token' => $request->headers->get('x-auth-token'),
        ]);

        $responseDataItem = [
            'id' => 1,
            'name' => 'Homer',
            'secondName' => 'Simpson',
            'roles' => ['guest'],
            'email' => 'homer@crud.com',
            'isEmailConfirmed' => true,
            'birthday' => '1965-05-12',
            'happyHour' => '14:00:00',
            'discount' => 30,
            'rating' => 3.5,
            'registeredAt' => '2000-10-11T19:57:31Z',
            'lastVisitedAt' => '665701200',
        ];
        $responseResolver = $factory->createForDefinition(CustomerFull::class);
        $responseResolved = $responseResolver->resolve($responseDataItem);

        return new JsonResponse([$responseResolved]);
    }

    public function create(Request $request, SwaggerResolverFactory $factory): JsonResponse
    {
        $requestResolver = $factory->createForRequest($request);
        $requestData = json_decode($request->getContent(), true);
        $requestData['x-auth-token'] = $request->headers->get('x-auth-token');
        $requestResolver->resolve($requestData);

        $responseData = ['id' => 1];
        $responseResolver = $factory->createForDefinition(ResponseCreated::class);
        $responseResolved = $responseResolver->resolve($responseData);

        return new JsonResponse($responseResolved);
    }

    public function getOne(): Response
    {
        return new Response();
    }

    public function update(): Response
    {
        return new Response();
    }

    public function updatePartial(): Response
    {
        return new Response();
    }

    public function delete(): Response
    {
        return new Response();
    }
}
