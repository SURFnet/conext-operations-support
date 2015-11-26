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

namespace Surfnet\Conext\OperationsSupportBundle\Guzzle\Middleware;

use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class LoggingMiddleware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            return $this->handle($handler, $request, $options);
        };
    }

    private function handle(callable $handler, RequestInterface $request, array $options)
    {
        $this->logger->debug(
            sprintf('Requesting %s %s', $request->getMethod(), $request->getUri()),
            ['headers' => $request->getHeaders()]
        );

        /** @var PromiseInterface $promise */
        $promise = $handler($request, $options);

        $promise->then(
            function (ResponseInterface $response) use ($request) {
                $this->logger->debug(
                    sprintf('Received HTTP %d', $response->getStatusCode()),
                    ['headers' => $request->getHeaders()]
                );
            },
            function (Exception $exception) {
                $this->logger->debug('HTTP request failed', ['exception' => $exception]);
            }
        );

        return $promise;
    }
}
