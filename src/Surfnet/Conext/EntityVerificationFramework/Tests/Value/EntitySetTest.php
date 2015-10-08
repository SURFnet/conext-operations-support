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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Value;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;

class EntitySetTest extends TestCase
{
    /**
     * @test
     * @group value
     * @dataProvider unequalSets
     *
     * @param array $set0
     * @param array $set1
     */
    public function it_can_test_for_inequality(array $set0, array $set1)
    {
        $this->assertFalse(
            (new EntitySet($set0))->equals(new EntitySet($set1)),
            "Entity sets should not be equal, but they're not"
        );
    }

    public function unequalSets()
    {
        return [
            [
                [new Entity(new EntityId('b'), EntityType::SP()), new Entity(new EntityId('a'), EntityType::IdP())],
                [new Entity(new EntityId('a'), EntityType::IdP())],
            ],
            [
                [new Entity(new EntityId('a'), EntityType::IdP()), new Entity(new EntityId('a'), EntityType::IdP())],
                [new Entity(new EntityId('b'), EntityType::SP())],
            ],
            [
                [new Entity(new EntityId('a'), EntityType::IdP())],
                [new Entity(new EntityId('a'), EntityType::SP()), new Entity(new EntityId('a'), EntityType::SP())],
            ],
            [
                [new Entity(new EntityId('a'), EntityType::IdP())],
                [],
            ],
            [
                [],
                [new Entity(new EntityId('a'), EntityType::IdP())],
            ],
        ];
    }

    /**
     * @test
     * @group value
     * @dataProvider equalSets
     *
     * @param array $set0
     * @param array $set1
     */
    public function it_can_test_for_equality(array $set0, array $set1)
    {
        $this->assertTrue(
            (new EntitySet($set0))->equals(new EntitySet($set1)),
            "Entity sets should be equal, but they're not"
        );
    }

    public function equalSets()
    {
        return [
            [
                [new Entity(new EntityId('a'), EntityType::IdP()), new Entity(new EntityId('a'), EntityType::IdP())],
                [new Entity(new EntityId('a'), EntityType::IdP())],
            ],
            [
                [new Entity(new EntityId('a'), EntityType::IdP())],
                [new Entity(new EntityId('a'), EntityType::IdP()), new Entity(new EntityId('a'), EntityType::IdP())],
            ],
            [
                [],
                [],
            ],
            [
                [new Entity(new EntityId('a'), EntityType::IdP()), new Entity(new EntityId('b'), EntityType::SP())],
                [new Entity(new EntityId('a'), EntityType::IdP()), new Entity(new EntityId('b'), EntityType::SP())],
            ],
            [
                [new Entity(new EntityId('a'), EntityType::IdP()), new Entity(new EntityId('b'), EntityType::SP()), new Entity(new EntityId('b'), EntityType::SP())],
                [new Entity(new EntityId('b'), EntityType::SP()),  new Entity(new EntityId('a'), EntityType::IdP())],
            ],
        ];
    }
}
