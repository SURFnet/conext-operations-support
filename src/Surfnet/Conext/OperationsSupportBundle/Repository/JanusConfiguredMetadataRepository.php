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

namespace Surfnet\Conext\OperationsSupportBundle\Repository;

use Psr\Log\LoggerInterface;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ConfiguredMetadataFactory;
use Surfnet\Conext\EntityVerificationFramework\Repository\ConfiguredMetadataRepository;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Exception\RuntimeException;
use Surfnet\JanusApiClientBundle\Service\ApiService;

final class JanusConfiguredMetadataRepository implements ConfiguredMetadataRepository, PublishedMetadataUrlRepository
{
    const CONNECTION_STATE_PRODUCTION = 'prodaccepted';

    /**
     * @var ApiService
     */
    private $apiService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array|null
     */
    private $entityConnectionIdMap;

    public function __construct(ApiService $apiService, LoggerInterface $logger)
    {
        $this->apiService = $apiService;
        $this->logger = $logger;
    }

    public function getMetadataFor(Entity $entity)
    {
        $this->logger->debug(sprintf('Fetching connection detail for entity "%s" from Janus', $entity));

        $id = $this->getConnectionIdForEntity($entity);
        $this->logger->debug(sprintf('Entity\'s connection id is "%d"', $id));

        $data = $this->apiService->read('connections/%d.json', [$id]);
        $this->logger->debug('Fetched connection detail');

        $metadata = ConfiguredMetadataFactory::deserialise($data);
        $this->logger->debug('Deserialised into configured metadata');

        return $metadata;
    }

    public function getPublishedMetadataUrlFor(Entity $entity)
    {
        $this->logger->debug(sprintf('Fetching published metadata URL for entity "%s" from Janus', $entity));

        $configuredMetadata = $this->getMetadataFor($entity);
        if (!$configuredMetadata->hasPublishedMetadataUrl()) {
            $this->logger->debug(sprintf('No published metadata URL is known for entity "%s"', $entity));

            return null;
        }

        $url = $configuredMetadata->getPublishedMetadataUrl();
        if (!$url->isValid()) {
            $this->logger->debug(sprintf('Published metadata URL "%s" is not valid, returning NULL', $url));

            return null;
        }

        $this->logger->debug(sprintf('Published metadata URL is "%s"', $url));

        return $url->getValidUrl();
    }

    public function getConfiguredEntities()
    {
        $this->logger->debug('Fetching connections from Janus');

        $data = $this->apiService->read('connections.json');

        $this->initialiseEntityConnectionIdMap($data);

        Assert::keyExists($data, 'connections');
        Assert::allIsArray($data['connections'], null, 'connections');
        Assert::allKeyExists($data['connections'], 'name', null, 'connections[]');
        Assert::allKeyExists($data['connections'], 'state', null, 'connections[]');
        Assert::allKeyExists($data['connections'], 'type', null, 'connections[]');

        $entities = [];
        foreach ($data['connections'] as $connection) {
            if ($connection['state'] !== self::CONNECTION_STATE_PRODUCTION) {
                $this->logger->debug(
                    sprintf(
                        'Skipping "%s" connection "%s", state is "%s" rather than "%s"',
                        $connection['type'],
                        $connection['name'],
                        $connection['state'],
                        self::CONNECTION_STATE_PRODUCTION
                    )
                );
                continue;
            }

            $entities[] = new Entity(new EntityId($connection['name']), new EntityType($connection['type']));
        }

        $this->logger->debug(sprintf('Fetched "%d" configured entities from Janus', count($entities)));

        return new EntitySet($entities);
    }

    /**
     * @param Entity $entity
     * @return int
     */
    private function getConnectionIdForEntity(Entity $entity)
    {
        if ($this->entityConnectionIdMap === null) {
            $this->logger->debug('Entity connection ID map is not initialised, writing map...');
            $this->initialiseEntityConnectionIdMap($this->apiService->read('connections.json'));
        }

        $entityKey = sprintf('%s:%s', $entity->getEntityId(), $entity->getEntityType());
        if (!isset($this->entityConnectionIdMap[$entityKey])) {
            throw new RuntimeException(sprintf('No connection ID is known for entity "%s"', $entity));
        }

        return $this->entityConnectionIdMap[$entityKey];
    }

    /**
     * Writes an entity/connection ID map based on Janus' connection list blob to `$this->entityConnectionIdMap`.
     *
     * @param array $connectionsData Connection list blob
     */
    private function initialiseEntityConnectionIdMap($connectionsData)
    {
        Assert::keyExists($connectionsData, 'connections');
        Assert::allIsArray($connectionsData['connections'], null, 'connections');
        Assert::allKeyExists($connectionsData['connections'], 'id', null, 'connections[]');
        Assert::allKeyExists($connectionsData['connections'], 'name', null, 'connections[]');
        Assert::allKeyExists($connectionsData['connections'], 'state', null, 'connections[]');
        Assert::allKeyExists($connectionsData['connections'], 'type', null, 'connections[]');

        $entityConnectionIdMap = [];
        foreach ($connectionsData['connections'] as $connection) {
            if ($connection['state'] !== self::CONNECTION_STATE_PRODUCTION) {
                $this->logger->debug(
                    sprintf(
                        'Skipping "%s" connection "%s", state is "%s" rather than "%s"',
                        $connection['type'],
                        $connection['name'],
                        $connection['state'],
                        self::CONNECTION_STATE_PRODUCTION
                    )
                );

                continue;
            }

            $entityKey = sprintf('%s:%s', new EntityId($connection['name']), new EntityType($connection['type']));
            $entityConnectionIdMap[$entityKey] = $connection['id'];
        }

        $this->entityConnectionIdMap = $entityConnectionIdMap;

        $this->logger->debug('Initialise entity connection ID map');
    }
}
