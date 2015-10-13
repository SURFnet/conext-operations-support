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

use Mockery as m;
use Mockery\Matcher\Closure as ClosureMatcher;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as UnitTest;
use Psr\Log\NullLogger;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationBlacklist;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest;
use Surfnet\Conext\EntityVerificationFramework\TestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;

class SuiteTest extends UnitTest
{
    /**
     * @test
     * @group EntityVerificationFramework
     * @group Suite
     */
    public function a_test_is_not_verified_when_it_is_skipped()
    {
        $testToSkip = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest');
        $testToSkip->shouldReceive('shouldBeSkipped')->andReturn(true);
        $testToSkip->shouldReceive('getReasonToSkip')->andReturn('Because I mocked it so');
        $testToSkip->shouldNotReceive('verify');

        $suite = $this->getSuite();

        $suite->addVerificationTest($testToSkip);

        $suite->verify($this->getMockContext(), $this->createBlacklistDummy());
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Suite
     *
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\LogicException
     * @expectedExceptionMessage did not return a VerificationTestResult
     */
    public function tests_must_return_a_verification_test_result()
    {
        $test = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest');
        $test->shouldReceive('shouldBeSkipped')->andReturn(false);
        $test->shouldReceive('verify')->andReturn(false);

        $suite = $this->getSuite();

        $suite->addVerificationTest($test);

        $suite->verify($this->getMockContext(), $this->createBlacklistDummy());
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Suite
     */
    public function the_suite_stops_after_the_first_failed_test_returning_a_failed_result()
    {
        $successfulTest = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest');
        $successfulTest->shouldReceive('shouldBeSkipped')->andReturn(false);
        $successfulTest->shouldReceive('verify')->andReturn(TestResult::success());

        $failedTest = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest');
        $failedTest->shouldReceive('shouldBeSkipped')->andReturn(false);
        $failedTest->shouldReceive('verify')->andReturn(TestResult::failed('reason', 'explanation', 3));

        $shouldNotBeCalled = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest');
        $shouldNotBeCalled->shouldNotReceive('shouldBeSkipped');
        $shouldNotBeCalled->shouldNotReceive('verify');

        $suite = $this->getSuite();

        $suite->addVerificationTest($successfulTest);
        $suite->addVerificationTest($failedTest);
        $suite->addVerificationTest($shouldNotBeCalled);

        $suiteResult = $suite->verify($this->getMockContext(), $this->createBlacklistDummy());
        $this->assertTrue($suiteResult->hasTestFailed());
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Suite
     */
    public function when_all_tests_are_successful_the_suite_is_successful()
    {
        $firstTest = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest');
        $firstTest->shouldReceive('shouldBeSkipped')->andReturn(false);
        $firstTest->shouldReceive('verify')->andReturn(TestResult::success());

        $secondTest = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest');
        $secondTest->shouldReceive('shouldBeSkipped')->andReturn(false);
        $secondTest->shouldReceive('verify')->andReturn(TestResult::success());

        $suite = $this->getSuite();

        $suite->addVerificationTest($firstTest);
        $suite->addVerificationTest($secondTest);

        $result = $suite->verify($this->getMockContext(), $this->createBlacklistDummy());
        $this->assertFalse($result->hasTestFailed());
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Suite
     */
    public function entities_can_be_excluded_from_tests_using_the_blacklist()
    {
        $firstTest = m::namedMock('SuiteTestBlacklistedTest', VerificationTest::class);
        $firstTest->shouldNotReceive('shouldBeSkipped');
        $firstTest->shouldNotReceive('verify');

        $secondTest = m::namedMock('SuiteTestNotBlacklistedTest', VerificationTest::class);
        $secondTest->shouldReceive('shouldBeSkipped')->andReturn(false);
        $secondTest->shouldReceive('verify')->andReturn(TestResult::success());

        $suite = $this->getSuite();
        $suite->addVerificationTest($firstTest);
        $suite->addVerificationTest($secondTest);

        $mockSp    = new Entity(new EntityId('mock'), EntityType::SP());
        $blacklist = m::mock(VerificationBlacklist::class);
        $blacklist->shouldReceive('isBlacklisted')->once()->with(self::eq($mockSp), 'suite_test_blacklisted_test')->andReturn(true);
        $blacklist->shouldReceive('isBlacklisted')->once()->with(self::eq($mockSp), 'suite_test_not_blacklisted_test')->andReturn(false);

        $result = $suite->verify($this->getMockContext(), $blacklist);
        $this->assertFalse($result->hasTestFailed());
    }

    /**
     * @return \Surfnet\Conext\EntityVerificationFramework\Suite mocked
     */
    private function getSuite()
    {
        $suite = m::mock('Surfnet\Conext\EntityVerificationFramework\Suite');
        $suite->shouldReceive('addVerificationTest')->passthru();
        $suite->shouldReceive('verify')->passthru();

        return $suite;
    }

    /**
     * @return \Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext mocked
     */
    private function getMockContext()
    {
        $context = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext');
        $context->shouldReceive('getLogger')->andReturn(new NullLogger());
        $context->shouldReceive('getEntity')->andReturn(new Entity(new EntityId('mock'), EntityType::SP()));

        return $context;
    }

    /**
     * @return MockInterface|VerificationBlacklist
     */
    private function createBlacklistDummy()
    {
        $blacklist = m::mock(VerificationBlacklist::class);
        $blacklist->shouldReceive('isBlacklisted')->andReturn(false);

        return $blacklist;
    }

    /**
     * @param mixed $expectedValueObject Value object with equals() method
     * @return ClosureMatcher
     */
    private static function eq($expectedValueObject)
    {
        return m::on(function ($actualValueObject) use ($expectedValueObject) {
            return $actualValueObject->equals($expectedValueObject);
        });
    }
}

