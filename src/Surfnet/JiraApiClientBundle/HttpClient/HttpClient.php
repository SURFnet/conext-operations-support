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

namespace Surfnet\JiraApiClientBundle\HttpClient;

use Surfnet\JiraApiClientBundle\HttpClient\Exception\CouldNotConnectToApi;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\RequestException;
use Jira_Api_Authentication_AuthenticationInterface as AuthenticationInterface;
use Jira_Api_Client_ClientInterface as JiraApiClientInterface;

class HttpClient implements JiraApiClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param $method
     * @param $url
     * @param array $data
     * @param $endpoint
     * @param AuthenticationInterface $credential
     * @return \Psr\Http\Message\StreamInterface
     */
    // @codingStandardsIgnoreStart (arguments with default values must be at the end; sadly, our JiraClient does not agree)
    public function sendRequest($method, $url, $data = array(), $endpoint, AuthenticationInterface $credential)
    {
        // @codingStandardsIgnoreEnd
        try {
            $response = $this->client->request($method, $endpoint.$url, [
                "auth" => [
                    $credential->getId(),
                    $credential->getPassword()
                ]
            ]);

        } catch (RequestException $requestException) {
            throw new CouldNotConnectToApi(sprintf(
                'Could not connect to JIRA API: %s. Request: %s.',
                $requestException->getMessage(),
                $requestException->getRequest()->getUri()
            ));
        }

        return $response->getBody();
    }
}
