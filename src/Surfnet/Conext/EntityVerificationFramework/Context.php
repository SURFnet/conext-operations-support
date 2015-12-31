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

namespace Surfnet\Conext\EntityVerificationFramework;

use Closure;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ConfiguredMetadata;
use Surfnet\Conext\EntityVerificationFramework\Metadata\PublishedMetadata;
use Surfnet\Conext\EntityVerificationFramework\Service\TlsService;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;

class Context implements VerificationContext
{
    /**
     * @var Entity
     */
    private $entity;

    /**
     * @var ConfiguredMetadata
     */
    private $configuredMetadata;

    /**
     * @var Closure
     */
    private $publishedMetadataCallable;

    /**
     * @var PublishedMetadata;
     */
    private $remoteMetadata;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var TlsService
     */
    private $tlsService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Entity $entity,
        ConfiguredMetadata $configuredMetadata,
        Closure $publishedMetadataCallable,
        ClientInterface $httpClient,
        TlsService $tlsService,
        LoggerInterface $logger
    ) {
        $this->entity                    = $entity;
        $this->configuredMetadata        = $configuredMetadata;
        $this->publishedMetadataCallable = $publishedMetadataCallable;
        $this->logger                    = $logger;
        $this->httpClient                = $httpClient;
        $this->tlsService                = $tlsService;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function hasRemoteMetadata()
    {
        if (!isset($this->remoteMetadata)) {
            $this->remoteMetadata = call_user_func($this->publishedMetadataCallable, $this->entity);
        }

        return $this->remoteMetadata !== null;
    }

    public function getRemoteMetadata()
    {
        if (!$this->hasRemoteMetadata()) {
            throw new LogicException(
                'Remote Metadata cannot be gotten as it does not exist, have you called "hasRemoteMetadata"'
                . ' to verify that the metadata exists?'
            );
        }

        return $this->remoteMetadata;
    }

    public function getConfiguredMetadata()
    {
        return $this->configuredMetadata;
    }

    /**
     * @return ClientInterface
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function getTlsService()
    {
        return $this->tlsService;
    }
}
