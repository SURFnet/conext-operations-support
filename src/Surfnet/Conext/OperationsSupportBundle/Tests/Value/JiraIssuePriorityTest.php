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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Value;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssuePriority;

/**
 * @runTestsInSeparateProcesses
 */
class JiraIssuePriorityTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function one_can_be_created_from_a_priority_id()
    {
        new JiraIssuePriority('10000');
    }

    /**
     * @test
     * @group value
     */
    public function priorities_can_be_compared_for_equality()
    {
        $priorityTrivial = new JiraIssuePriority('10000');
        $priority10000 = new JiraIssuePriority('10000');
        $priority10046 = new JiraIssuePriority('10046');

        $this->assertTrue(
            $priorityTrivial->equals($priority10000),
            'Priority of severity trivial should  equal priority for ID "10000"'
        );
        $this->assertFalse(
            $priorityTrivial->equals($priority10046),
            'Priority of severity trivial should not equal priority for ID "10046"'
        );
    }

    /**
     * @test
     * @group value
     */
    public function an_unmapped_priority_can_be_created()
    {
        new JiraIssuePriority('10038');
    }

    /**
     * @test
     * @group value
     * @dataProvider nonDigitStrings
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     * @expectedExceptionMessage Priority ID must consist of one or more digits
     *
     * @param mixed $nonDigitString
     */
    public function an_unmapped_priority_doesnt_accept_an_invalid_priority_id($nonDigitString)
    {
        new JiraIssuePriority($nonDigitString);
    }

    public function nonDigitStrings()
    {
        return [
            'empty'    => [''],
            'alphanum' => ['192abc'],
            'int'      => [1],
            'int 0'    => [0],
            'float'    => [1.23],
            'float 0'  => [0.0],
            'null'     => [null],
            'bool'     => [false],
            'array'    => [[]],
            'object'   => [new \stdClass],
            'resource' => [fopen('php://memory', 'w')],
        ];
    }
}
