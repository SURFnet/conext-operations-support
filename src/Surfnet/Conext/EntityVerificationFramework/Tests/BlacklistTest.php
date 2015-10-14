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

namespace Surfnet\Conext\EntityVerificationFramework\Tests;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Blacklist;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;

final class BlacklistTest extends TestCase
{
    const SUITE_ONE = 'one_suite';
    const SUITE_TWO = 'two_suite';
    const SUITE_ONE_TEST_ONE = 'one_suite.one_test';

    /**
     * @test
     * @group blacklist
     * @dataProvider blacklisted
     *
     * @param Blacklist $blacklist
     * @param Entity    $entity
     * @param string    $suiteName
     */
    public function blacklists_by_suite_name(Blacklist $blacklist, Entity $entity, $suiteName)
    {
        $this->assertTrue(
            $blacklist->isBlacklisted($entity, $suiteName),
            sprintf(
                'Entity "%s" is blacklisted for "%s", but blacklist implementation reports that it isn\'t',
                $entity,
                $suiteName
            )
        );
    }

    public function blacklisted()
    {
        return [
            '*(RUG), RUG in one?' => [
                new Blacklist([Blacklist::WILDCARD => new EntitySet([$this->entitySpRug()])]),
                $this->entitySpRug(),
                self::SUITE_ONE,
            ],
            '*(RUG, HvA), RUG in one?' => [
                new Blacklist([Blacklist::WILDCARD => new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()])]),
                $this->entitySpRug(),
                self::SUITE_ONE,
            ],
            'one(HU), *(RUG, HvA), HU in one?' => [
                new Blacklist([self::SUITE_ONE => new EntitySet([$this->entitySpHU()]), Blacklist::WILDCARD => new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()])]),
                $this->entitySpHU(),
                self::SUITE_ONE,
            ],
            'one(HU), *(RUG, HvA), RUG in one?' => [
                new Blacklist([self::SUITE_ONE => new EntitySet([$this->entitySpHU()]), Blacklist::WILDCARD => new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()])]),
                $this->entitySpRug(),
                self::SUITE_ONE,
            ],
            'one(HU), two(RUG, HvA), HvA in two?' => [
                new Blacklist([self::SUITE_ONE => new EntitySet([$this->entitySpHU()]), self::SUITE_TWO => new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()])]),
                $this->entitySpRug(),
                self::SUITE_TWO,
            ],
            'one.one(HU), two(RUG, HvA), HU in one.one?' => [
                new Blacklist([self::SUITE_ONE_TEST_ONE => new EntitySet([$this->entitySpHU()]), self::SUITE_TWO => new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()])]),
                $this->entitySpHU(),
                self::SUITE_ONE_TEST_ONE,
            ],
        ];
    }

    /**
     * @test
     * @group blacklist
     * @dataProvider notBlacklisted
     *
     * @param Blacklist $blacklist
     * @param Entity    $entity
     * @param string    $suiteName
     */
    public function doesnt_blacklist_by_suite_name(Blacklist $blacklist, Entity $entity, $suiteName)
    {
        $this->assertFalse(
            $blacklist->isBlacklisted($entity, $suiteName),
            sprintf(
                'Entity "%s" is not blacklisted for "%s", but blacklist implementation reports that it is',
                $entity,
                $suiteName
            )
        );
    }

    public function notBlacklisted()
    {
        return [
            '*(RUG), HU in one?' => [
                new Blacklist([Blacklist::WILDCARD => new EntitySet([$this->entitySpRug()])]),
                $this->entitySpHU(),
                self::SUITE_ONE,
            ],
            '*(), RUG in one?' => [
                new Blacklist([], new EntitySet()),
                $this->entitySpRug(),
                self::SUITE_ONE,
            ],
            'one(HU), *(RUG, HvA), HU in two?' => [
                new Blacklist([self::SUITE_ONE => new EntitySet([$this->entitySpHU()]), Blacklist::WILDCARD => new EntitySet([$this->entitySpRug(), $this->entityIdPHvA()])]),
                $this->entitySpHU(),
                self::SUITE_TWO,
            ],
            'one(HU), RUG in one?' => [
                new Blacklist([self::SUITE_ONE => new EntitySet([$this->entitySpHU()])]),
                $this->entitySpRug(),
                self::SUITE_ONE,
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
