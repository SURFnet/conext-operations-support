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
use PHPUnit_Framework_TestCase as UnitTest;
use Psr\Log\NullLogger;
use Surfnet\Conext\EntityVerificationFramework\Runner;
use Surfnet\Conext\EntityVerificationFramework\SuiteResult;
use Surfnet\Conext\EntityVerificationFramework\TestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityCollection;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\Conext\EntityVerificationFramework\Value\ConfiguredMetadata;

class RunnerTest extends UnitTest
{
    /**
     * @var EntityCollection
     */
    private static $entities;

    /**
     * @var \Surfnet\Conext\EntityVerificationFramework\Repository\ConfiguredMetadataRepository mocked
     */
    private $configuredMetadataRepository;

    /**
     * @var \Surfnet\Conext\EntityVerificationFramework\Repository\PublishedMetadataRepository mocked
     */
    private $publishedMetadataRepository;

    /**
     * @var \Surfnet\Conext\EntityVerificationFramework\Runner
     */
    private $runner;

    public static function setUpBeforeClass()
    {
        static::$entities = new EntityCollection([
            new Entity(new EntityId('mock'), EntityType::SP()),
            new Entity(new EntityId('mock'), EntityType::IdP()),
        ]);
    }

    public function setUp()
    {
        $configuredMetadata = m::mock(ConfiguredMetadata::class);

        $this->configuredMetadataRepository = m::mock(
            'Surfnet\Conext\EntityVerificationFramework\Repository\ConfiguredMetadataRepository'
        );
        $this->configuredMetadataRepository->shouldReceive('getConfiguredEntities')->andReturn(static::$entities);
        $this->configuredMetadataRepository->shouldReceive('getMetadataFor')->andReturn($configuredMetadata);

        $this->publishedMetadataRepository = m::mock(
            'Surfnet\Conext\EntityVerificationFramework\Repository\PublishedMetadataRepository'
        );
        $this->publishedMetadataRepository->shouldReceive('getMetadataFor')->andReturnNull();

        $this->runner = new Runner(
            $this->configuredMetadataRepository,
            $this->publishedMetadataRepository,
            new NullLogger()
        );
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Runner
     */
    public function a_suite_does_not_run_when_it_is_skipped()
    {
        $suiteToSkip = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $suiteToSkip->shouldReceive('shouldBeSkipped')->andReturn(true);
        $suiteToSkip->shouldReceive('getReasonToSkip')->andReturn('Because I mocked it so');
        $suiteToSkip->shouldNotReceive('verify');

        $this->runner->addVerificationSuite($suiteToSkip);

        $this->runner->run($this->getMockReporter());
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Runner
     */
    public function a_suite_does_not_run_when_it_is_not_whitelisted()
    {
        $ignoredSuite = m::namedMock('MockedIgnoredSuite', 'Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $ignoredSuite->shouldNotReceive('shouldBeSkipped');
        $ignoredSuite->shouldNotReceive('verify');

        $runSuite = m::namedMock('MockedRunSuite','Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $runSuite->shouldReceive('shouldBeSkipped')->andReturn(false);
        $runSuite->shouldReceive('verify')->andReturn(SuiteResult::success());

        $this->runner->addVerificationSuite($ignoredSuite);
        $this->runner->addVerificationSuite($runSuite);

        $whitelist = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteWhitelist');
        $whitelist->shouldReceive('contains')->with('mocked_ignored_suite')->atLeast()->once()->andReturn(false);
        $whitelist->shouldReceive('contains')->with('mocked_run_suite')->atLeast()->once()->andReturn(true);

        $this->runner->run($this->getMockReporter(), $whitelist);
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Runner
     *
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\LogicException
     * @expectedExceptionMessage did not return a VerificationSuiteResult
     */
    public function suites_must_return_a_verification_suite_result()
    {
        $suite = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $suite->shouldReceive('shouldBeSkipped')->andReturn(false);
        $suite->shouldReceive('verify')->andReturn(false);

        $this->runner->addVerificationSuite($suite);
        $this->runner->run($this->getMockReporter());
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Runner
     */
    public function when_a_suite_fails_the_following_suites_are_still_verified()
    {
        $firstSuite = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $firstSuite->shouldReceive('shouldBeSkipped')->andReturn(false);
        $firstSuite->shouldReceive('verify')->andReturn(SuiteResult::success());

        $failedResult = SuiteResult::failedTest(
            TestResult::failed('reason', 'explanation', 3),
            'evf.verification'
        );
        $failingSuite = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $failingSuite->shouldReceive('shouldBeSkipped')->andReturn(false);
        $failingSuite->shouldReceive('verify')->andReturn($failedResult);

        $lastSuite = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $lastSuite->shouldReceive('shouldBeSkipped')->andReturn(false);
        $lastSuite->shouldReceive('verify')->andReturn(SuiteResult::success());

        $reporter = $this->getMockReporter();
        $reporter->shouldReceive('reportFailedVerificationFor');

        $this->runner->addVerificationSuite($firstSuite);
        $this->runner->addVerificationSuite($failingSuite);
        $this->runner->addVerificationSuite($lastSuite);

        $this->runner->run($reporter);
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Runner
     */
    public function every_entity_is_verified()
    {
        $count = count(static::$entities);

        $failedResult = SuiteResult::failedTest(
            TestResult::failed('reason', 'explanation', 3),
            'evf.verification'
        );
        $failingSuite = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $failingSuite->shouldReceive('shouldBeSkipped')->times($count)->andReturn(false);
        $failingSuite->shouldReceive('verify')->times($count)->andReturn($failedResult);

        $reporter = $this->getMockReporter();
        $reporter->shouldReceive('reportFailedVerificationFor')->times($count);

        $this->runner->addVerificationSuite($failingSuite);
        $this->runner->run($reporter);
    }

    private function getMockReporter()
    {
        return m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter');
    }
}
