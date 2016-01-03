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
use Surfnet\Conext\EntityVerificationFramework\Value\Host;
use Surfnet\Conext\EntityVerificationFramework\Value\HostSet;

class HostSetTest extends TestCase
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
            (new HostSet($set0))->equals(new HostSet($set1)),
            "Host sets are not equal, but are reported to be"
        );
    }

    public function unequalSets()
    {
        return [
            [
                [new Host('b', 443), new Host('a', 443)],
                [new Host('a', 443)],
            ],
            [
                [new Host('a', 443), new Host('a', 443)],
                [new Host('b', 443)],
            ],
            [
                [new Host('a', 443)],
                [new Host('a', 443), new Host('a', 8080)],
            ],
            [
                [new Host('a', 443)],
                [],
            ],
            [
                [],
                [new Host('a', 443)],
            ],
            [
                [new Host('a', 133)],
                [new Host('a', 443)],
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
            (new HostSet($set0))->equals(new HostSet($set1)),
            "Host sets should be equal, but they're not"
        );
    }

    public function equalSets()
    {
        return [
            [
                [new Host('a', 443), new Host('a', 443)],
                [new Host('a', 443)],
            ],
            [
                [new Host('a', 443)],
                [new Host('a', 443), new Host('a', 443)],
            ],
            [
                [],
                [],
            ],
            [
                [new Host('a', 443), new Host('b', 443)],
                [new Host('a', 443), new Host('b', 443)],
            ],
            [
                [new Host('a', 443), new Host('b', 443), new Host('b', 443)],
                [new Host('b', 443),  new Host('a', 443)],
            ],
        ];
    }

    /**
     * @test
     * @group value
     * @dataProvider setsThatContainAHost
     */
    public function sets_can_contain_hosts(HostSet $set, Host $host)
    {
        $this->assertTrue(
            $set->contains($host),
            sprintf('Set of %d should contain host "%s", but HostSet reports otherwise', count($set), $host)
        );
    }

    public function setsThatContainAHost()
    {
        return [
            '1-set' => [
                new HostSet([$this->hostOne(), $this->hostTwo()]),
                $this->hostTwo(),
            ],
            '2-set, first' => [
                new HostSet([$this->hostOne(), $this->hostTwo()]),
                $this->hostOne(),
            ],
            '2-set, second' => [
                new HostSet([$this->hostOne(), $this->hostTwo()]),
                $this->hostTwo(),
            ],
        ];
    }

    /**
     * @test
     * @group value
     * @dataProvider setsThatDontContainAnHost
     *
     * @param HostSet $set
     * @param Host    $host
     */
    public function sets_can_not_contain_hosts(HostSet $set, Host $host)
    {
        $this->assertFalse(
            $set->contains($host),
            sprintf('Set of %d should not contain host "%s", but HostSet reports otherwise', count($set), $host)
        );
    }

    public function setsThatDontContainAnHost()
    {
        return [
            '2-set' => [
                new HostSet([]),
                $this->hostOne(),
            ],
            '1-set' => [
                new HostSet([$this->hostOne()]),
                $this->hostTwo(),
            ],
        ];
    }

    /**
     * @return Host
     */
    private function hostOne()
    {
        return new Host('one.example', 443);
    }

    /**
     * @return Host
     */
    private function hostTwo()
    {
        return new Host('two.example', 133);
    }
}
