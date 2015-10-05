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

namespace Surfnet\JiraApiClientBundle\Tests;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use Jira_Api_Authentication_Anonymous;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\JiraApiClientBundle\HttpClient\HttpClient;

class HttpClientTest extends TestCase
{
    /**
     * @test
     */
    public function map_request_exception_to_custom_exception()
    {
        $request = new Request("POST", "fakeurl.fake");
        $requestException = new RequestException("FakeError", $request);

        $expectedMessage = sprintf('Could not connect to JIRA API: %s. Request: %s.',
            $requestException->getMessage(),
            $requestException->getRequest()->getUri()
        );
        $this->setExpectedException('Surfnet\JiraApiClientBundle\HttpClient\Exception\CouldNotConnectToApi', $expectedMessage);

        $guzzleClient = m::mock('GuzzleHttp\Client');
        $guzzleClient->shouldReceive("request")
            ->once()
            ->andThrow($requestException);

        $httpClient = new HttpClient($guzzleClient);
        $httpClient->sendRequest(
            "POST",
            "fakeurl.fake",
            null,
            "",
            new Jira_Api_Authentication_Anonymous()
        );
    }
}
