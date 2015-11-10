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

use GuzzleHttp\ClientInterface;
use Mockery as m;
use Mockery\Matcher\Closure as ClosureMatcher;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as UnitTest;
use Psr\Log\NullLogger;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationBlacklist;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite;
use Surfnet\Conext\EntityVerificationFramework\ContextFactory;
use Surfnet\Conext\EntityVerificationFramework\Runner;
use Surfnet\Conext\EntityVerificationFramework\SuiteResult;
use Surfnet\Conext\EntityVerificationFramework\TestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\ConfiguredMetadata;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;
use Surfnet\Conext\EntityVerificationFramework\Value\EntitySet;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;
use Surfnet\VerificationSuite\NameResolverTestSuite\NameResolverTestSuite;

class RunnerTest extends UnitTest
{
    /**
     * @var EntitySet
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

    /**
     * @var VerificationBlacklist|MockInterface
     */
    private $blacklist;

    public static function setUpBeforeClass()
    {
        self::$entities = new EntitySet([
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

        $this->blacklist = m::mock(VerificationBlacklist::class);

        $this->runner = new Runner(
            $this->configuredMetadataRepository,
            $this->blacklist,
            new ContextFactory(
                $this->configuredMetadataRepository,
                $this->publishedMetadataRepository,
                m::mock(ClientInterface::class)
            ),
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
        $this->blacklistNothing();

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
        $this->blacklistNothing();

        $ignoredSuite = new NameResolverTestSuite();

        $runSuite = m::namedMock('MockedRunSuite','Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite');
        $runSuite->shouldReceive('shouldBeSkipped')->andReturn(false);
        $runSuite->shouldReceive('verify')->andReturn(SuiteResult::success());

        $this->runner->addVerificationSuite($ignoredSuite);
        $this->runner->addVerificationSuite($runSuite);

        $whitelist = m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteWhitelist');
        $whitelist->shouldReceive('contains')->with('name_resolver_test_suite')->atLeast()->once()->andReturn(false);
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
        $this->blacklistNothing();

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
        $this->blacklistNothing();

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
        $this->blacklistNothing();

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

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Runner
     */
    public function entities_can_be_excluded_from_suites_using_the_blacklist()
    {
        $verificationContext = m::type(VerificationContext::class);

        $suiteOne = m::namedMock('RunnerTestBlacklistedSuite', VerificationSuite::class);
        $suiteOne->shouldNotReceive('verify');
        $suiteOne->shouldNotReceive('shouldBeSkipped');
        $suiteOne->shouldNotReceive('getReasonToSkip');

        $suiteTwo = m::namedMock('RunnerTestNotBlacklistedSuite', VerificationSuite::class);
        $suiteTwo->shouldReceive('shouldBeSkipped')->twice()->with($verificationContext)->andReturn(false);
        $suiteTwo
            ->shouldReceive('verify')
            ->twice()
            ->with($verificationContext, $this->blacklist)
            ->andReturn(SuiteResult::success());

        $this->runner->addVerificationSuite($suiteOne);
        $this->runner->addVerificationSuite($suiteTwo);

        $mockSp = new Entity(new EntityId('mock'), EntityType::SP());
        $mockIdp = new Entity(new EntityId('mock'), EntityType::IdP());

        $this->blacklist
            ->shouldReceive('isBlacklisted')
            ->once()
            ->with(self::eq($mockSp), 'runner_test_blacklisted_suite')
            ->andReturn(true);
        $this->blacklist
            ->shouldReceive('isBlacklisted')
            ->once()
            ->with(self::eq($mockIdp), 'runner_test_blacklisted_suite')
            ->andReturn(true);
        $this->blacklist
            ->shouldReceive('isBlacklisted')
            ->once()
            ->with(self::eq($mockSp), 'runner_test_not_blacklisted_suite')
            ->andReturn(false);
        $this->blacklist
            ->shouldReceive('isBlacklisted')
            ->once()
            ->with(self::eq($mockIdp), 'runner_test_not_blacklisted_suite')
            ->andReturn(false);

        $this->runner->run($this->getMockReporter());
    }

    private function getMockReporter()
    {
        return m::mock('Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter');
    }

    private function blacklistNothing()
    {
        $this->blacklist->shouldReceive('isBlacklisted')->andReturn(false);
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
