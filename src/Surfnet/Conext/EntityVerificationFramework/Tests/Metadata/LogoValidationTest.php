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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Logo;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;
use Symfony\Component\HttpFoundation\Response;

class LogoValidationTest extends TestCase
{
    /**
     * @test
     * @group value
     * @dataProvider logosWithViolations
     *
     * @param Logo   $logo
     * @param string $violation
     */
    public function logo_can_be_invalid(Logo $logo, $violation)
    {
        /** @var MockInterface|ResponseInterface $response200 */
        $response200 = m::mock(ResponseInterface::class);
        $response200->shouldReceive('getStatusCode')->andReturn(Response::HTTP_OK);
        /** @var MockInterface|ClientInterface $httpClient */
        $httpClient = m::mock(ClientInterface::class);
        $httpClient->shouldReceive('request')->andReturn($response200);
        $context = new ConfiguredMetadataValidationContext($httpClient);

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate')->with(m::type(Url::class), $context);
        $validator
            ->shouldReceive('addViolation')
            ->with($violation)
            ->once();

        $logo->validate($validator, $context);
    }

    public function logosWithViolations()
    {
        return [
            'logo URL not hosted by SURFconext' => [
                Logo::deserialise(['url' => 'https://logo.invalid/', 'width' => '100', 'height' => '100'], 'propPath'),
                'Logo URL "https://logo.invalid/" does not match https://static.surfconext.nl/logos/idp/<name>.png'
            ],
            'logo width not a stringy number' => [
                Logo::deserialise(['url' => 'https://static.surfconext.nl/logos/idp/test.png', 'width' => 'dd', 'height' => '100'], 'propPath'),
                'Logo width "dd" is invalid: must be a number larger than 0'
            ],
            'logo width lower than 1' => [
                Logo::deserialise(['url' => 'https://static.surfconext.nl/logos/idp/test.png', 'width' => '0', 'height' => '100'], 'propPath'),
                'Logo width "0" is invalid: must be a number larger than 0'
            ],
            'logo height not a stringy number' => [
                Logo::deserialise(['url' => 'https://static.surfconext.nl/logos/idp/test.png', 'width' => '100', 'height' => 'dd'], 'propPath'),
                'Logo height "dd" is invalid: must be a number larger than 0'
            ],
            'logo height lower than 1' => [
                Logo::deserialise(['url' => 'https://static.surfconext.nl/logos/idp/test.png', 'width' => '100', 'height' => '0'], 'propPath'),
                'Logo height "0" is invalid: must be a number larger than 0'
            ],
        ];
    }
}
