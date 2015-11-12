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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Repository;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Psr\Log\NullLogger;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ConfiguredMetadata;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ConfiguredMetadataFactory;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Repository\JanusConfiguredMetadataRepository;
use Surfnet\JanusApiClientBundle\Service\ApiService;

class JanusConfiguredMetadataRepositoryTest extends TestCase
{
    /**
     * @test
     * @group metadata
     * @dataProvider connectionsData
     *
     * @param array $connectionsData
     * @param Entity[] $expectedEntities
     */
    public function configured_entities_can_be_listed(array $connectionsData, array $expectedEntities)
    {
        /** @var ApiService|MockInterface $apiService */
        $apiService = m::mock(ApiService::class);
        $apiService
            ->shouldReceive('read')
            ->with('connections.json')
            ->once()
            ->andReturn(['connections' => $connectionsData]);

        $repository = new JanusConfiguredMetadataRepository($apiService, new NullLogger());

        $actualEntitySet   = $repository->getConfiguredEntities();
        $expectedEntitySet = new EntitySet($expectedEntities);

        $this->assertTrue(
            $actualEntitySet->equals($expectedEntitySet),
            "Configured entities don't match expected entities"
        );
    }

    public function connectionsData()
    {
        $rugSso  = 'https://rug.invalid/sso';
        $docsAcs = 'https://docs.invalid/acs';

        return [
            '0 (0 prod, 1 test)' => [
                [
                    [
                        'id'    => 1,
                        'name'  => $rugSso,
                        'state' => 'testaccepted',
                        'type'  => 'saml20-idp',
                    ],
                ],
                [],
            ],
            '0 (0 prod, 0 test)' => [
                [],
                [],
            ],
            '1 (1 prod, 0 test)' => [
                [
                    [
                        'id'    => 1,
                        'name'  => $rugSso,
                        'state' => 'prodaccepted',
                        'type'  => 'saml20-idp',
                    ],
                ],
                [
                    new Entity(new EntityId($rugSso), EntityType::IdP()),
                ],
            ],
            '2 (2 prod, 0 test)' => [
                [
                    [
                        'id'    => 1,
                        'name'  => $rugSso,
                        'state' => 'prodaccepted',
                        'type'  => 'saml20-idp',
                    ],
                    [
                        'id'    => 1,
                        'name'  => $docsAcs,
                        'state' => 'prodaccepted',
                        'type'  => 'saml20-sp',
                    ],
                ],
                [
                    new Entity(new EntityId($rugSso), EntityType::IdP()),
                    new Entity(new EntityId($docsAcs), EntityType::SP()),
                ],
            ],
        ];
    }

    /**
     * @test
     * @group metadata
     * @dataProvider connectionsWithMissingNameStateOrType
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param array $connectionsData
     */
    public function name_state_type_must_be_present(array $connectionsData)
    {
        /** @var ApiService|MockInterface $apiService */
        $apiService = m::mock(ApiService::class);
        $apiService
            ->shouldReceive('read')
            ->with('connections.json')
            ->once()
            ->andReturn(['connections' => $connectionsData]);

        $repository = new JanusConfiguredMetadataRepository($apiService, new NullLogger());
        $repository->getConfiguredEntities();
    }

    public function connectionsWithMissingNameStateOrType()
    {
        return [
            'id missing' => [
                [['name' => 'https://uva.invalid/sso', 'state' => 'prodaccepted', 'type' => 'saml20-idp']],
            ],
            'name missing' => [
                [['id' => 1, 'state' => 'prodaccepted', 'type' => 'saml20-idp']],
            ],
            'state missing' => [
                [['id' => 1, 'name' => 'https://uva.invalid/sso', 'type' => 'saml20-idp']],
            ],
            'type missing' => [
                [['id' => 1, 'name' => 'https://uva.invalid/sso', 'state' => 'prodaccepted']],
            ],
        ];
    }

    /**
     * @test
     * @group metadata
     * @runInSeparateProcess
     */
    public function metadata_for_an_entity_can_be_fetched()
    {
        $rugSso  = 'https://rug.invalid/sso';
        $docsAcs = 'https://docs.invalid/acs';

        $connectionsData = [
            [
                'id'    => 1,
                'name'  => $rugSso,
                'state' => 'prodaccepted',
                'type'  => 'saml20-idp',
            ],
            [
                'id'    => 2,
                'name'  => $docsAcs,
                'state' => 'prodaccepted',
                'type'  => 'saml20-sp',
            ],
        ];

        $connectionData = [];

        /** @var ApiService|MockInterface $apiService */
        $apiService = m::mock(ApiService::class);
        $apiService
            ->shouldReceive('read')
            ->with('connections.json')
            ->once()
            ->andReturn(['connections' => $connectionsData]);
        $apiService
            ->shouldReceive('read')
            ->with('connections/%d.json', [1])
            ->once()
            ->andReturn($connectionData);

        $metadata = m::mock(ConfiguredMetadata::class);
        m::mock('alias:' . ConfiguredMetadataFactory::class)
            ->shouldReceive('deserialise')
            ->with($connectionData)
            ->once()
            ->andReturn($metadata);

        $repository = new JanusConfiguredMetadataRepository($apiService, new NullLogger());
        $actualMetadata = $repository->getMetadataFor(new Entity(new EntityId($rugSso), EntityType::IdP()));

        $this->assertSame($actualMetadata, $metadata);
    }

    /**
     * @test
     * @group metadata
     * @runInSeparateProcess
     */
    public function doesnt_need_to_fetch_list_of_connections_more_than_once_to_determine_entitys_connection_id()
    {
        $rugSso  = 'https://rug.invalid/sso';
        $docsAcs = 'https://docs.invalid/acs';

        $connectionsData = [
            [
                'id'    => 1,
                'name'  => $rugSso,
                'state' => 'prodaccepted',
                'type'  => 'saml20-idp',
            ],
            [
                'id'    => 2,
                'name'  => $docsAcs,
                'state' => 'prodaccepted',
                'type'  => 'saml20-sp',
            ],
        ];

        $connectionData = [];

        /** @var ApiService|MockInterface $apiService */
        $apiService = m::mock(ApiService::class);
        $apiService
            ->shouldReceive('read')
            ->with('connections.json')
            ->once()
            ->andReturn(['connections' => $connectionsData]);
        $apiService->shouldReceive('read')->with('connections/%d.json', [1])->once()->andReturn($connectionData);
        $apiService->shouldReceive('read')->with('connections/%d.json', [2])->once()->andReturn($connectionData);

        $metadata = m::mock(ConfiguredMetadata::class);
        m::mock('alias:' . ConfiguredMetadataFactory::class)
            ->shouldReceive('deserialise')
            ->with($connectionData)
            ->once()
            ->andReturn($metadata);

        $repository = new JanusConfiguredMetadataRepository($apiService, new NullLogger());
        $repository->getMetadataFor(new Entity(new EntityId($rugSso), EntityType::IdP()));
        $repository->getMetadataFor(new Entity(new EntityId($docsAcs), EntityType::SP()));
    }

    /**
     * @test
     * @group metadata
     * @runInSeparateProcess
     */
    public function doesnt_need_to_fetch_list_of_connections_to_determine_entitys_connection_id_when_connections_have_already_been_fetched()
    {
        $rugSso  = 'https://rug.invalid/sso';
        $docsAcs = 'https://docs.invalid/acs';

        $connectionsData = [
            [
                'id'    => 1,
                'name'  => $rugSso,
                'state' => 'prodaccepted',
                'type'  => 'saml20-idp',
            ],
            [
                'id'    => 2,
                'name'  => $docsAcs,
                'state' => 'prodaccepted',
                'type'  => 'saml20-sp',
            ],
        ];

        $connectionData = [];

        /** @var ApiService|MockInterface $apiService */
        $apiService = m::mock(ApiService::class);
        $apiService
            ->shouldReceive('read')
            ->with('connections.json')
            ->once()
            ->andReturn(['connections' => $connectionsData]);
        $apiService->shouldReceive('read')->with('connections/%d.json', [1])->once()->andReturn($connectionData);
        $apiService->shouldReceive('read')->with('connections/%d.json', [2])->once()->andReturn($connectionData);

        $metadata = m::mock(ConfiguredMetadata::class);
        m::mock('alias:' . ConfiguredMetadataFactory::class)
            ->shouldReceive('deserialise')
            ->with($connectionData)
            ->once()
            ->andReturn($metadata);

        $repository = new JanusConfiguredMetadataRepository($apiService, new NullLogger());
        $repository->getConfiguredEntities();
        $repository->getMetadataFor(new Entity(new EntityId($docsAcs), EntityType::SP()));
    }

    /**
     * @test
     * @group metadata
     * @expectedException \Surfnet\Conext\OperationsSupportBundle\Exception\RuntimeException
     * @expectedExceptionMessage No connection ID is known for entity "https://hu.invalid[saml20-sp]"
     * @runInSeparateProcess
     */
    public function throws_an_exception_when_fetching_metadata_for_an_entity_that_cannot_be_mapped_to_a_connection_id()
    {
        $rugSso  = 'https://rug.invalid/sso';
        $docsAcs = 'https://docs.invalid/acs';

        $connectionsData = [
            [
                'id'    => 1,
                'name'  => $rugSso,
                'state' => 'prodaccepted',
                'type'  => 'saml20-idp',
            ],
            [
                'id'    => 2,
                'name'  => $docsAcs,
                'state' => 'prodaccepted',
                'type'  => 'saml20-sp',
            ],
        ];

        /** @var ApiService|MockInterface $apiService */
        $apiService = m::mock(ApiService::class);
        $apiService
            ->shouldReceive('read')
            ->with('connections.json')
            ->once()
            ->andReturn(['connections' => $connectionsData]);

        $repository = new JanusConfiguredMetadataRepository($apiService, new NullLogger());
        $repository->getMetadataFor(new Entity(new EntityId('https://hu.invalid'), EntityType::SP()));
    }
}
