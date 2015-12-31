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

namespace Surfnet\TlsBundle\Tests\Service;

use DateTimeImmutable;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url as MetadataUrl;
use Surfnet\Conext\EntityVerificationFramework\Value\X509Certificate;
use Surfnet\TlsBundle\OpenSsl\Client;
use Surfnet\TlsBundle\OpenSsl\Client\GetEndUserCertificateResult;
use Surfnet\TlsBundle\Parser\X509CertificateParser;
use Surfnet\TlsBundle\Service\TlsService;
use Surfnet\TlsBundle\Value\Url;

class TlsServiceTest extends TestCase
{
    /**
     * @test
     * @group TlsBundle
     * @group Service
     */
    public function certificates_get_be_fetched()
    {
        $url                 = 'https://domain.example';
        $certificateString   = 'CERT';
        $certificateResult   = GetEndUserCertificateResult::success($certificateString);
        $expectedCertificate = new X509Certificate('987a52', new DateTimeImmutable('2017-01-02 03:04:05'));

        /** @var MockInterface|Client $openSslClient */
        $openSslClient = m::mock(Client::class);
        $openSslClient
            ->shouldReceive('getEndUserCertificateForUrl')
            ->once()
            ->with(m::anyOf(Url::fromString($url)))
            ->andReturn($certificateResult);
        
        /** @var MockInterface|X509CertificateParser $certificateParser */
        $certificateParser = m::mock(X509CertificateParser::class);
        $certificateParser
            ->shouldReceive('parseString')
            ->once()
            ->with($certificateString)
            ->andReturn($expectedCertificate);

        $service = new TlsService($openSslClient, $certificateParser);
        $result = $service->getEndUserCertificateForUrl(MetadataUrl::fromString($url));

        $this->assertTrue($result->wasSuccessful());
        $this->assertTrue($expectedCertificate->equals($result->getCertificate()));
    }
}
