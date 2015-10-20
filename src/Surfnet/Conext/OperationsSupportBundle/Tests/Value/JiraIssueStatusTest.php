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
use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;

class JiraIssueStatusTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_can_be_created()
    {
        new JiraIssueStatus('10000');
        new JiraIssueStatus('0');
    }

    /**
     * @test
     * @group value
     */
    public function statuses_can_be_equal()
    {
        $status10000a = new JiraIssueStatus('10000');
        $status10000b = new JiraIssueStatus('10000');
        $status0 = new JiraIssueStatus('0');

        $this->assertTrue($status10000a->equals($status10000b), 'Statuses "10000" and "10000" should be equal');
        $this->assertFalse($status10000a->equals($status0), 'Statuses "10000" and "0" should not be equal');
    }

    /**
     * @test
     * @group value
     * @dataProvider nonDigitStrings
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $nonDigitString
     */
    public function it_doesnt_accept_nondigit_strings($nonDigitString)
    {
        new JiraIssueStatus($nonDigitString);
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

    /**
     * @test
     * @group value
     * @runInSeparateProcess
     */
    public function a_status_can_be_muted()
    {
        JiraIssueStatus::configureMutedStatus(new JiraIssueStatus('10000'));

        $this->assertTrue((new JiraIssueStatus('10000'))->isMuted(), 'Status ID 10000 should represent a muted status');
        $this->assertFalse((new JiraIssueStatus('39845'))->isMuted(), "Status ID 39845 shouldn't represent a muted status");
    }

    /**
     * @test
     * @group value
     * @expectedException \Surfnet\Conext\OperationsSupportBundle\Exception\LogicException
     * @runInSeparateProcess
     */
    public function a_status_can_not_be_muted_if_the_muted_state_has_not_been_configured()
    {
        (new JiraIssueStatus('10000'))->isMuted();
    }
}
