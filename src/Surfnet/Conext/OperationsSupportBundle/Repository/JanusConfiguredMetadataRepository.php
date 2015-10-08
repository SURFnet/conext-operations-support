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

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Repository\ConfiguredMetadataRepository;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;
use Surfnet\JanusApiClientBundle\Service\ApiService;

final class JanusConfiguredMetadataRepository implements ConfiguredMetadataRepository
{
    const CONNECTION_STATE_PRODUCTION = 'prodaccepted';

    /**
     * @var ApiService
     */
    private $apiService;

    public function __construct(ApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function getMetadataFor(Entity $entity)
    {
        throw new LogicException('This dummy implementation cannot get metadata for an entity just yet');
    }

    public function getConfiguredEntities()
    {
        $data = $this->apiService->read('connections.json');

        Assert::keyExists($data, 'connections');
        Assert::allIsArray($data['connections'], null, 'connections');
        Assert::allKeyExists($data['connections'], 'name', null, 'connections[]');
        Assert::allKeyExists($data['connections'], 'state', null, 'connections[]');
        Assert::allKeyExists($data['connections'], 'type', null, 'connections[]');

        $entities = [];
        foreach ($data['connections'] as $connection) {
            if ($connection['state'] !== self::CONNECTION_STATE_PRODUCTION) {
                continue;
            }

            $entities[] = new Entity(new EntityId($connection['name']), new EntityType($connection['type']));
        }

        return new EntitySet($entities);
    }
}
