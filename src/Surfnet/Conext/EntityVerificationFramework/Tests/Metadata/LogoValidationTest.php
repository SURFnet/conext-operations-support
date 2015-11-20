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
use PHPUnit_Framework_TestCase as TestCase;
use Mockery as m;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Logo;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\Validator;

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
    public function it_can_be_invalid(Logo $logo, $violation)
    {
        /** @var MockInterface|ResponseInterface $response200 */
        $response200 = m::mock(ResponseInterface::class);
        $response200->shouldReceive('getStatusCode')->andReturn(200);
        /** @var MockInterface|ClientInterface $httpClient */
        $httpClient = m::mock(ClientInterface::class);
        $httpClient->shouldReceive('request')->andReturn($response200);
        $context = new ValidationContext($httpClient);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
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

    /**
     * @test
     * @group value
     */
    public function its_url_can_be_invalid()
    {
        $url = '###';

        /** @var MockInterface|ValidationContext $context */
        $context = m::mock(ValidationContext::class);
        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        $validator
            ->shouldReceive('validate')
            ->with(
                m::on(function (Url $actualUrl) use ($url) {
                    return (string) $actualUrl === $url;
                }),
                $context
            )
            ->once();

        $logo = Logo::deserialise(['url' => $url, 'width' => '100', 'height' => '1225'], 'propPath');
        $logo->validate($validator, $context);
    }

    /**
     * @test
     * @group value
     */
    public function its_url_can_be_unavailable()
    {
        $url = 'https://static.surfconext.nl/logos/idp/test.png';

        /** @var MockInterface|ResponseInterface $response */
        $response = m::mock(ResponseInterface::class);
        $response->shouldReceive('getStatusCode')->andReturn(404);
        /** @var MockInterface|ClientInterface $httpClient */
        $httpClient = m::mock(ClientInterface::class);
        $httpClient->shouldReceive('request')->with('GET', $url)->once()->andReturn($response);
        $context = new ValidationContext($httpClient);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        $validator->shouldReceive('validate')->with(m::type(Url::class), $context);
        $validator
            ->shouldReceive('addViolation')
            ->with(sprintf('Logo "%s" is not available, server returned status code %d', $url, 404))
            ->once();

        $logo = Logo::deserialise(['url' => $url, 'width' => '100', 'height' => '1225'], 'propPath');
        $logo->validate($validator, $context);
    }
}
