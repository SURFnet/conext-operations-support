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

namespace Surfnet\Conext\OperationsSupportBundle\Tests\Reporter;

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\SuiteResult;
use Surfnet\Conext\EntityVerificationFramework\TestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\OperationsSupportBundle\Reporter\CliReporter;
use Symfony\Component\Console\Output\OutputInterface;

final class CliReporterTest extends TestCase
{
    /**
     * @test
     * @group reporter
     */
    public function it_writes_a_report_to_output()
    {
        $output = m::mock(OutputInterface::class);
        $output->shouldReceive('writeln')->atLeast()->once();

        $reporter = new CliReporter($output);
        $reporter->reportFailedVerificationFor(
            new Entity(new EntityId('meh'), EntityType::SP()),
            SuiteResult::failedTest(
                TestResult::failed('reason', 'explanation', TestResult::SEVERITY_MEDIUM),
                'my_suite.my_test'
            )
        );
    }

    /**
     * @test
     * @group reporter
     * @expectedException \Surfnet\Conext\OperationsSupportBundle\Exception\LogicException
     */
    public function it_should_throw_an_exception_when_reporting_a_successful_result()
    {
        $output = m::mock(OutputInterface::class);
        $output->shouldNotReceive('writeln');

        $reporter = new CliReporter($output);
        $reporter->reportFailedVerificationFor(
            new Entity(new EntityId('meh'), EntityType::SP()),
            SuiteResult::success()
        );
    }
}
