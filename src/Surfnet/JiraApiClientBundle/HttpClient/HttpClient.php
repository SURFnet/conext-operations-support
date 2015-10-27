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

use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Jira_Api_Authentication_AuthenticationInterface as AuthenticationInterface;
use Jira_Api_Client_ClientInterface as JiraApiClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class HttpClient implements JiraApiClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param HttpClientInterface $client
     * @param LoggerInterface $logger
     */
    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    // @codingStandardsIgnoreStart (arguments with default values must be at the end; sadly, our JiraClient does not agree)
    public function sendRequest($method, $url, $data = array(), $endpoint, AuthenticationInterface $credential)
    {
    // @codingStandardsIgnoreEnd
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($data),
        ];

        if ($credential->getId() || $credential->getPassword()) {
            $options['auth'] = [
                $credential->getId(),
                $credential->getPassword(),
            ];
        }

        $this->logger->info(
            sprintf('Sending %s request to %s', $method, $endpoint . $url),
            ['payload' => $options['body']]
        );

        try {
            $response = $this->client->request($method, $endpoint . $url, $options);
        } catch (ClientException $e) {
            $response = $e->getResponse();

            if ($response) {
                $this->logger->error(
                    sprintf('Received HTTP %d from JIRA API ("%s")', $response->getStatusCode(), $e->getMessage()),
                    ['payload' => $this->readResponseBodyContents($response)]
                );
            } else {
                $this->logger->error(
                    sprintf('Unknown client error occurred while communicating with JIRA ("%s")', $e->getMessage())
                );
            }

            throw $e;
        } catch (GuzzleException $e) {
            $this->logger->error(
                sprintf('Guzzle exception occurred while communicating with JIRA ("%s")', $e->getMessage())
            );

            throw $e;
        }

        $responseContents = $this->readResponseBodyContents($response);
        $this->logger->info('Received response from JIRA API', ['payload' => $responseContents]);

        return $responseContents;
    }

    /**
     * @param ResponseInterface $response
     * @return null|string
     */
    private function readResponseBodyContents(ResponseInterface $response)
    {
        $body = $response->getBody();

        if ($body->isSeekable()) {
            $contents = $body->getContents();
            $body->seek(0);
        } else {
            $contents = null;
        }

        return $contents;
    }
}
