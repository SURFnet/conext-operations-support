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

use GuzzleHttp\ClientInterface as HttpClientInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Mockery as m;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Surfnet\Conext\EntityVerificationFramework\Metadata\SupportUrl;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;
use Symfony\Component\HttpFoundation\Response;

class SupportUrlTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     * @dataProvider supportUrlsMissingLocales
     *
     * @param SupportUrl $supportUrl
     * @param string[]   $violations
     */
    public function support_urls_are_required_to_have_en_nl_locales(SupportUrl $supportUrl, $violations)
    {
        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate');

        foreach ($violations as $violation) {
            $validator
                ->shouldReceive('addViolation')
                ->with($violation)
                ->once();
        }

        $supportUrl->validate($validator, $context);
    }

    public function supportUrlsMissingLocales()
    {
        return [
            'No locales' => [
                new SupportUrl(),
                ['Support URL must have locales "en","nl" configured, has ""']
            ],
            'NL locale missing' => [
                new SupportUrl(['en' => Url::fromString('')]),
                ['Support URL must have locales "en","nl" configured, has "en"']
            ],
            'EN locale missing' => [
                new SupportUrl(['nl' => Url::fromString('')]),
                ['Support URL must have locales "en","nl" configured, has "nl"']
            ],
            'No locales missing' => [
                new SupportUrl(['en' => Url::fromString(''), 'nl' => Url::fromString('')]),
                []
            ],
        ];
    }

    /**
     * @test
     * @group Metadata
     */
    public function support_urls_are_validated()
    {
        $urlNl = Url::fromString('nl');
        $urlEn = Url::fromString('en');

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate')->with($urlNl, $context)->once();
        $validator->shouldReceive('validate')->with($urlEn, $context)->once();

        $supportUrl = new SupportUrl(['nl' => $urlNl, 'en' => $urlEn]);
        $supportUrl->validate($validator, $context);
    }

    /**
     * @test
     * @group Metadata
     */
    public function valid_support_urls_availability_is_checked()
    {
        $urlNl = Url::fromString('https://voorbeeld.invalid');
        $urlEn = Url::fromString('https://example.invalid');

        /** @var MockInterface|ResponseInterface $response404 */
        $response404 = m::mock(ResponseInterface::class);
        $response404->shouldReceive('getStatusCode')->andReturn(Response::HTTP_NOT_FOUND);
        /** @var MockInterface|HttpClientInterface $httpClient */
        $httpClient = m::mock(HttpClientInterface::class);
        $httpClient->shouldReceive('request')->with('GET', $urlNl->getValidUrl())->andReturn($response404);
        $httpClient->shouldReceive('request')->with('GET', $urlEn->getValidUrl())->andReturn($response404);

        $context = new ConfiguredMetadataValidationContext($httpClient);

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate');
        $validator
            ->shouldReceive('addViolation')
            ->with('Support URL is not available ("https://voorbeeld.invalid"), server returned status code 404')
            ->once();
        $validator
            ->shouldReceive('addViolation')
            ->with('Support URL is not available ("https://example.invalid"), server returned status code 404')
            ->once();

        $supportUrl = new SupportUrl(['nl' => $urlNl, 'en' => $urlEn]);
        $supportUrl->validate($validator, $context);
    }
}
