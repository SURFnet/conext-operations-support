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

namespace Surfnet\TlsBundle\Tests\OpenSsl;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\NullLogger;
use Surfnet\TlsBundle\OpenSsl\CliClient;
use Surfnet\TlsBundle\Value\Url;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

class CliClientTest extends TestCase
{
    /**
     * @test
     * @group TlsBundle
     * @runInSeparateProcess Mocks ProcessBuilder::create()
     */
    public function an_end_user_certificate_can_be_fetched()
    {
        $connectionInformation = 'Connection information';
        $expectedCertificate = '---CERT---';

        $sClientProcess = m::mock(Process::class);
        $sClientProcess->shouldReceive('run')->once()->with()->andReturn(0);
        $sClientProcess->shouldReceive('getOutput')->with()->andReturn($connectionInformation);
        $sClientProcess->shouldReceive('stop');

        $sClientProcessBuilder = m::mock('FakeProcessBuilder');
        $sClientProcessBuilder->shouldReceive('setInput')->with('')->andReturn($sClientProcessBuilder);
        $sClientProcessBuilder->shouldReceive('getProcess')->with()->andReturn($sClientProcess);

        $staticProcessBuilder = m::mock('alias:' . ProcessBuilder::class);
        $staticProcessBuilder
            ->shouldReceive('create')
            ->once()
            ->with(['openssl', 's_client', '-connect', 'idp.example:443'])
            ->andReturn($sClientProcessBuilder);

        $x509Process = m::mock(Process::class);
        $x509Process->shouldReceive('run')->once()->with()->andReturn(0);
        $x509Process->shouldReceive('getOutput')->with()->andReturn($expectedCertificate);
        $x509Process->shouldReceive('stop');

        $x509ProcessBuilder = m::mock('FakeProcessBuilder');
        $x509ProcessBuilder->shouldReceive('setInput')->with($connectionInformation)->andReturn($x509ProcessBuilder);
        $x509ProcessBuilder->shouldReceive('getProcess')->with()->andReturn($x509Process);

        $staticProcessBuilder
            ->shouldReceive('create')
            ->once()
            ->with(['openssl', 'x509', '-inform', 'pem', '-outform', 'pem'])
            ->andReturn($x509ProcessBuilder);

        $client = new CliClient(new NullLogger());
        $result = $client->getEndUserCertificateForUrl(Url::fromString('https://idp.example'));

        $this->assertTrue($result->wasSuccessful(), 'Fetch of end-user certificate should be successful');
        $this->assertSame($expectedCertificate, $result->getCertificate());
    }

    /**
     * @test
     * @group TlsBundle
     * @runInSeparateProcess Mocks ProcessBuilder::create()
     */
    public function ssl_connection_failure_is_reported()
    {
        $sClientProcess = m::mock(Process::class);
        $sClientProcess->shouldReceive('run')->once()->with()->andReturn(1);
        $sClientProcess->shouldReceive('getErrorOutput')->with()->andReturn('gethostbyname failure');
        $sClientProcess->shouldReceive('stop');

        $sClientProcessBuilder = m::mock('FakeProcessBuilder');
        $sClientProcessBuilder->shouldReceive('setInput')->with('')->andReturn($sClientProcessBuilder);
        $sClientProcessBuilder->shouldReceive('getProcess')->with()->andReturn($sClientProcess);

        $staticProcessBuilder = m::mock('alias:' . ProcessBuilder::class);
        $staticProcessBuilder
            ->shouldReceive('create')
            ->once()
            ->with(['openssl', 's_client', '-connect', 'idp.example:443'])
            ->andReturn($sClientProcessBuilder);

        $client = new CliClient(new NullLogger());
        $result = $client->getEndUserCertificateForUrl(Url::fromString('https://idp.example'));

        $this->assertFalse($result->wasSuccessful(), 'Fetch of end-user certificate should not be successful');
        $this->assertTrue($result->didConnectionFail(), 'Connection to SSL endpoint should have failed');
    }

    /**
     * @test
     * @group TlsBundle
     * @runInSeparateProcess Mocks ProcessBuilder::create()
     */
    public function certificate_extraction_failure_is_reported()
    {
        $connectionInformation = 'Connection information';

        $sClientProcess = m::mock(Process::class);
        $sClientProcess->shouldReceive('run')->once()->with()->andReturn(0);
        $sClientProcess->shouldReceive('getOutput')->with()->andReturn($connectionInformation);
        $sClientProcess->shouldReceive('stop');

        $sClientProcessBuilder = m::mock('FakeProcessBuilder');
        $sClientProcessBuilder->shouldReceive('setInput')->with('')->andReturn($sClientProcessBuilder);
        $sClientProcessBuilder->shouldReceive('getProcess')->with()->andReturn($sClientProcess);

        $staticProcessBuilder = m::mock('alias:' . ProcessBuilder::class);
        $staticProcessBuilder
            ->shouldReceive('create')
            ->once()
            ->with(['openssl', 's_client', '-connect', 'idp.example:443'])
            ->andReturn($sClientProcessBuilder);

        $x509Process = m::mock(Process::class);
        $x509Process->shouldReceive('run')->once()->with()->andReturn(1);
        $x509Process->shouldReceive('getErrorOutput')->with()->andReturn('unable to load certificate');
        $x509Process->shouldReceive('stop');

        $x509ProcessBuilder = m::mock('FakeProcessBuilder');
        $x509ProcessBuilder->shouldReceive('setInput')->with($connectionInformation)->andReturn($x509ProcessBuilder);
        $x509ProcessBuilder->shouldReceive('getProcess')->with()->andReturn($x509Process);

        $staticProcessBuilder
            ->shouldReceive('create')
            ->once()
            ->with(['openssl', 'x509', '-inform', 'pem', '-outform', 'pem'])
            ->andReturn($x509ProcessBuilder);

        $client = new CliClient(new NullLogger());
        $result = $client->getEndUserCertificateForUrl(Url::fromString('https://idp.example'));

        $this->assertFalse($result->wasSuccessful(), 'Fetch of end-user certificate should not be successful');
        $this->assertTrue($result->didCertificateExtractionFail(), 'Extraction of certificate should have failed');
    }
}
