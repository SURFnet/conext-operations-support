<?php

/**
 * Copyright 2015 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Metadata;

use GuzzleHttp\ClientInterface;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Http\Message\ResponseInterface;
use Surfnet\Conext\EntityVerificationFramework\Metadata\LogoUrl;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;
use Symfony\Component\HttpFoundation\Response;

class LogoUrlValidationTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     */
    public function logo_url_must_be_hosted_on_static_dot_surfconext_dot_nl()
    {
        $url = 'https://cdn.invalid/logo.bmp';

        /** @var MockInterface|ResponseInterface $response200 */
        $response200 = m::mock(ResponseInterface::class);
        $response200->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        /** @var MockInterface|ClientInterface $httpClient */
        $httpClient = m::mock(ClientInterface::class);
        $httpClient->shouldReceive('request')->andReturn($response200);
        $context = new ConfiguredMetadataValidationContext($httpClient);

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator
            ->shouldReceive('addViolation')
            ->with(sprintf('Logo URL "%s" does not match https://static.surfconext.nl/logos/idp/<name>.png', $url))
            ->once();

        $logoUrl = LogoUrl::fromString($url);
        $logoUrl->validate($validator, $context);
    }

    /**
     * @test
     * @group Metadata
     */
    public function logo_url_can_be_unavailable()
    {
        $url = 'https://static.surfconext.nl/logos/idp/test.png';

        /** @var MockInterface|ResponseInterface $response */
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(Response::HTTP_NOT_FOUND);
        /** @var MockInterface|ClientInterface $httpClient */
        $httpClient = m::mock(ClientInterface::class);
        $httpClient->shouldReceive('request')->with('GET', $url)->once()->andReturn($response);
        $context = new ConfiguredMetadataValidationContext($httpClient);

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate')->with(m::type(Url::class), $context);
        $validator
            ->shouldReceive('addViolation')
            ->with(sprintf('Logo "%s" is not available, server returned status code %d', $url, Response::HTTP_NOT_FOUND))
            ->once();

        $logoUrl = LogoUrl::fromString($url);
        $logoUrl->validate($validator, $context);
    }
}
