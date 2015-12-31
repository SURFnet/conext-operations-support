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

namespace Surfnet\TlsBundle\Tests\Parser;

use DateTimeImmutable;
use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Value\X509Certificate;
use Surfnet\TlsBundle\OpenSsl\OpenSsl;
use Surfnet\TlsBundle\Parser\OpenSslX509CertificateParser;

class X509CertificateParserTest extends TestCase
{
    /**
     * @test
     * @group TlsBundle
     */
    public function certificates_can_be_parsed()
    {
        $certificateString   = '----CERT----';
        $validUntilTimestamp = 147481600;
        $certificateInfo     = ['validTo_time_t' => $validUntilTimestamp];

        /** @var MockInterface|OpenSsl $openSsl */
        $openSsl = m::mock(OpenSsl::class);
        $openSsl
            ->shouldReceive('x509Parse')
            ->with($certificateString, OpenSsl::X509_PARSE_LONG_NAMES)
            ->andReturn($certificateInfo);
        $openSsl->shouldReceive('x509Fingerprint')->with($certificateString)->andReturn('0123abcd');
        $openSsl->shouldNotReceive('errorString');

        $parser = new OpenSslX509CertificateParser($openSsl);

        $expectedCertificate = new X509Certificate('0123abcd', new DateTimeImmutable('@' . $validUntilTimestamp));
        $actualCertificate   = $parser->parseString($certificateString);

        $this->assertTrue($expectedCertificate->equals($actualCertificate));
    }

    /**
     * @test
     * @group TlsBundle
     * @expectedException \Surfnet\TlsBundle\Parser\Exception\ParseException
     * @expectedExceptionMessageRegExp ~cannot be parsed.+ERR1.+ERR2~
     */
    public function an_exception_is_thrown_when_the_certificate_cannot_be_parsed()
    {
        $certificateString = '----CERT----';

        /** @var MockInterface|OpenSsl $openSsl */
        $openSsl = m::mock(OpenSsl::class);
        $openSsl
            ->shouldReceive('x509Parse')
            ->with($certificateString, OpenSsl::X509_PARSE_LONG_NAMES)
            ->andReturn(false);
        $openSsl->shouldReceive('errorString')->once()->withNoArgs()->andReturn('ERR1');
        $openSsl->shouldReceive('errorString')->once()->withNoArgs()->andReturn('ERR2');
        $openSsl->shouldReceive('errorString')->once()->withNoArgs()->andReturn(false);

        $parser = new OpenSslX509CertificateParser($openSsl);
        $parser->parseString($certificateString);
    }

    /**
     * @test
     * @group TlsBundle
     * @expectedException \Surfnet\TlsBundle\Parser\Exception\ParseException
     * @expectedExceptionMessage Certificate contains no valid-to date
     */
    public function an_exception_is_thrown_when_the_certificate_info_contains_no_validto_date()
    {
        $certificateString = '----CERT----';
        $certificateInfo   = [];

        /** @var MockInterface|OpenSsl $openSsl */
        $openSsl = m::mock(OpenSsl::class);
        $openSsl
            ->shouldReceive('x509Parse')
            ->with($certificateString, OpenSsl::X509_PARSE_LONG_NAMES)
            ->andReturn($certificateInfo);
        $openSsl->shouldReceive('x509Fingerprint')->with($certificateString)->andReturn('0123abcd');
        $openSsl->shouldNotReceive('errorString');

        $parser = new OpenSslX509CertificateParser($openSsl);
        $parser->parseString($certificateString);
    }

    /**
     * @test
     * @group TlsBundle
     * @dataProvider nonInts
     * @expectedException \Surfnet\TlsBundle\Parser\Exception\ParseException
     * @expectedExceptionMessage Expected valid-to timestamp to be a string, got
     */
    public function an_exception_is_thrown_when_the_certificate_info_contains_an_invalid_validto_date($nonInt)
    {
        $certificateString = '----CERT----';
        $certificateInfo   = ['validTo_time_t' => $nonInt];

        /** @var MockInterface|OpenSsl $openSsl */
        $openSsl = m::mock(OpenSsl::class);
        $openSsl
            ->shouldReceive('x509Parse')
            ->with($certificateString, OpenSsl::X509_PARSE_LONG_NAMES)
            ->andReturn($certificateInfo);
        $openSsl->shouldReceive('x509Fingerprint')->with($certificateString)->andReturn('0123abcd');
        $openSsl->shouldNotReceive('errorString');

        $parser = new OpenSslX509CertificateParser($openSsl);
        $parser->parseString($certificateString);
    }

    public function nonInts()
    {
        return [
            'empty string'      => [''],
            'string'            => ['abcd efgh'],
            'numerical string'  => ['5446854'],
            'array'             => [array()],
            'object'            => [new \stdClass()],
            'writable resource' => [fopen('php://memory', 'w')],
            'null'              => [null],
        ];
    }

    /**
     * @test
     * @group TlsBundle
     * @expectedException \Surfnet\TlsBundle\Parser\Exception\ParseException
     * @expectedExceptionMessageRegExp ~fingerprint could not be calculated.+ERR1~
     */
    public function an_exception_is_thrown_when_the_fingerprint_cannot_be_calculated()
    {
        $certificateString = '----CERT----';
        $certificateInfo   = ['validTo_time_t' => 1457481600];

        /** @var MockInterface|OpenSsl $openSsl */
        $openSsl = m::mock(OpenSsl::class);
        $openSsl
            ->shouldReceive('x509Parse')
            ->with($certificateString, OpenSsl::X509_PARSE_LONG_NAMES)
            ->andReturn($certificateInfo);
        $openSsl->shouldReceive('x509Fingerprint')->with($certificateString)->andReturn(false);
        $openSsl->shouldReceive('errorString')->once()->withNoArgs()->andReturn('ERR1');
        $openSsl->shouldReceive('errorString')->once()->withNoArgs()->andReturn(false);

        $parser = new OpenSslX509CertificateParser($openSsl);
        $parser->parseString($certificateString);
    }
}
