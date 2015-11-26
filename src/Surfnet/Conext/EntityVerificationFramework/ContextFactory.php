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

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Surfnet\Conext\EntityVerificationFramework\Repository\ConfiguredMetadataRepository;
use Surfnet\Conext\EntityVerificationFramework\Repository\PublishedMetadataRepository;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;

final class ContextFactory
{
    /**
     * @var ConfiguredMetadataRepository
     */
    private $configuredMetadataRepository;

    /**
     * @var PublishedMetadataRepository
     */
    private $publishedMetadataRepository;

    /**
     * An HTTP client that can be used by tests.
     *
     * @var ClientInterface
     */
    private $testHttpClient;

    public function __construct(
        ConfiguredMetadataRepository $configuredMetadataRepository,
        PublishedMetadataRepository $publishedMetadataRepository,
        ClientInterface $testHttpClient
    ) {
        $this->configuredMetadataRepository = $configuredMetadataRepository;
        $this->publishedMetadataRepository  = $publishedMetadataRepository;
        $this->testHttpClient               = $testHttpClient;
    }

    /**
     * @param Entity          $entity
     * @param LoggerInterface $logger
     * @return Context
     */
    public function create(Entity $entity, LoggerInterface $logger)
    {
        return new Context(
            $entity,
            $this->configuredMetadataRepository->getMetadataFor($entity),
            function (Entity $entity) {
                return $this->publishedMetadataRepository->getMetadataFor($entity);
            },
            $this->testHttpClient,
            $logger
        );
    }
}
