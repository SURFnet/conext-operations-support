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

namespace Surfnet\JanusApiClientBundle\Tests\Service;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\JanusApiClientBundle\Service\ApiService;

class ApiServiceTest extends TestCase
{
    /**
     * @test
     * @group JanusApiClientBundle
     * @group ApiService
     */
    public function throw_exception_when_json_cannot_be_parsed()
    {
        $this->setExpectedException('\Surfnet\JanusApiClientBundle\Exception\MalformedResponseException');

        $response = m::mock('GuzzleHttp\Message\ResponseInterface')
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200)
            ->shouldReceive('getBody')
            ->once()
            ->andReturn('invalid json')
            ->getMock();

        $guzzle = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('request')
            ->once()
            ->with('GET', '/resource', m::any())
            ->andReturn($response)
            ->getMock();

        $service = new ApiService($guzzle);
        $service->read('/resource');
    }

    /**
     * @test
     * @group JanusApiClientBundle
     * @group ApiService
     */
    public function throw_exception_when_resource_cannot_be_found()
    {
        $this->setExpectedException('\Surfnet\JanusApiClientBundle\Exception\ResourceNotFoundException');

        $response = m::mock('GuzzleHttp\Message\ResponseInterface')
            ->shouldReceive('getStatusCode')
            ->andReturn(404)
            ->getMock();

        $guzzle = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('request')
            ->with('GET', '/resource', m::any())
            ->andReturn($response)
            ->getMock();

        $service = new ApiService($guzzle);
        $service->read('/resource');
    }

    /**
     * @test
     * @dataProvider notAllowedStatusCodeProvider
     * @group JanusApiClientBundle
     * @group ApiService
     */
    public function throw_exception_when_status_code_is_not_200($statusCode)
    {
        $this->setExpectedException('\Surfnet\JanusApiClientBundle\Exception\InvalidResponseException');

        $response = m::mock('GuzzleHttp\Message\ResponseInterface')
            ->shouldReceive('getStatusCode')
            ->andReturn($statusCode)
            ->getMock();

        $guzzle = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('request')
            ->with('GET', '/resource', m::any())
            ->andReturn($response)
            ->getMock();

        $service = new ApiService($guzzle);
        $service->read('/resource');
    }

    /**
     * @test
     * @dataProvider notAllowedStatusCodeProvider
     * @group JanusApiClientBundle
     * @group ApiService
     */
    public function throw_exception_when_empty_resource()
    {
        $this->setExpectedException('\RuntimeException');

        $response = m::mock('GuzzleHttp\Message\ResponseInterface')
            ->shouldReceive('getStatusCode')
            ->andReturn(200)
            ->getMock();

        $guzzle = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('request')
            ->with('GET', '', m::any())
            ->andReturn($response)
            ->getMock();

        $service = new ApiService($guzzle);
        $service->read('');
    }

    /**
     * @test
     * @group JanusApiClientBundle
     * @group ApiService
     */
    public function format_resource_parameters()
    {
        $response = m::mock('GuzzleHttp\Message\ResponseInterface')
            ->shouldReceive('getStatusCode')
            ->once()
            ->andReturn(200)
            ->shouldReceive('getBody')
            ->once()
            ->getMock();

        $guzzle = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('request')
            ->once()
            ->with('GET', '/resource/John%2FDoe', m::any())
            ->andReturn($response)
            ->getMock();

        $service = new ApiService($guzzle);
        $service->read('/resource/%s', ['John/Doe']);
    }
    
    /**
     * @test
     * @group JanusApiClientBundle
     * @group ApiService
     */
    public function pass_request_to_guzzle()
    {
        $response = m::mock('GuzzleHttp\Message\ResponseInterface')
            ->shouldReceive('getStatusCode')
            ->andReturn(200)
            ->shouldReceive('getBody')
            ->getMock();

        $guzzle = m::mock('GuzzleHttp\ClientInterface')
            ->shouldReceive('request')
            ->with('GET', '/resource', m::any())
            ->andReturn($response)
            ->getMock();

        $api = new ApiService($guzzle);
        $api->read("/resource");
    }

    public function notAllowedStatusCodeProvider()
    {
        return [
            [300],
            [400],
            [500],
        ];
    }
}
