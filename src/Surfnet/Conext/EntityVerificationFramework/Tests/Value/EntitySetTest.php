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
            "Entity sets are not equal, but are reported to be"
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

    /**
     * @test
     * @group value
     * @dataProvider setsThatContainAnEntity
     */
    public function sets_can_contain_entities(EntitySet $set, Entity $entity)
    {
        $this->assertTrue(
            $set->contains($entity),
            sprintf('Set of %d should contain entity "%s", but EntitySet reports otherwise', count($set), $entity)
        );
    }

    public function setsThatContainAnEntity()
    {
        return [
            '1-set' => [
                new EntitySet([$this->entitySpRug()]),
                $this->entitySpRug(),
            ],
            '2-set, first' => [
                new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()]),
                $this->entitySpRug(),
            ],
            '2-set, second' => [
                new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()]),
                $this->entityIdPHvA(),
            ],
        ];
    }

    /**
     * @test
     * @group value
     * @dataProvider setsThatDontContainAnEntity
     */
    public function sets_can_not_contain_entities(EntitySet $set, Entity $entity)
    {
        $this->assertFalse(
            $set->contains($entity),
            sprintf('Set of %d should not contain entity "%s", but EntitySet reports otherwise', count($set), $entity)
        );
    }

    public function setsThatDontContainAnEntity()
    {
        return [
            '2-set' => [
                new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()]),
                $this->entitySpHU(),
            ],
            '0-set' => [
                new EntitySet([]),
                $this->entitySpRug(),
            ],
            '1-set' => [
                new EntitySet([$this->entitySpRug()]),
                $this->entityIdPHvA(),
            ],
        ];
    }

    /**
     * @return Entity
     */
    private function entitySpRug()
    {
        return new Entity(new EntityId('RUG'), EntityType::SP());
    }

    /**
     * @return Entity
     */
    private function entityIdPHvA()
    {
        return new Entity(new EntityId('HvA'), EntityType::IdP());
    }

    /**
     * @return Entity
     */
    private function entitySpHU()
    {
        return new Entity(new EntityId('HU'), EntityType::SP());
    }
}
