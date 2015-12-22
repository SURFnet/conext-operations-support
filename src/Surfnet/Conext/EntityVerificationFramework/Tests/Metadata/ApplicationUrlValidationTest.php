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
use Surfnet\Conext\EntityVerificationFramework\Metadata\ApplicationUrl;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;
use Symfony\Component\HttpFoundation\Response;

class ApplicationUrlValidationTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     */
    public function logo_url_can_be_unavailable()
    {
        $url = 'https://app.invalid';

        /** @var MockInterface|ResponseInterface $response */
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(Response::HTTP_NOT_FOUND);
        /** @var MockInterface|ClientInterface $httpClient */
        $httpClient = m::mock(ClientInterface::class);
        $httpClient->shouldReceive('request')->with('GET', $url)->once()->andReturn($response);
        $context = new ConfiguredMetadataValidationContext($httpClient);

        /** @var MockInterface|ConfiguredMetadataVisitor $visitor */
        $visitor = m::mock(ConfiguredMetadataVisitor::class);
        $visitor->shouldReceive('visit')->with(m::type(Url::class), $context);

        /** @var MockInterface|ConfiguredMetadataConstraintViolationWriter $violations */
        $violations = m::mock(ConfiguredMetadataConstraintViolationWriter::class);
        $violations
            ->shouldReceive('add')
            ->with(
                sprintf(
                    'Application URL "%s" is not available, server returned status code %d',
                    $url,
                    Response::HTTP_NOT_FOUND
                )
            )
            ->once();

        $logoUrl = ApplicationUrl::fromString($url);
        $logoUrl->validate($visitor, $violations, $context);
    }
}
