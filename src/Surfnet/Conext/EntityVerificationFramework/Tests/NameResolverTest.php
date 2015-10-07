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

use PHPUnit_Framework_TestCase as UnitTest;
use Surfnet\Conext\EntityVerificationFramework\NameResolver;
use Mockery as m;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;
use Surfnet\VerificationSuite\NameResolverTestSuite\NameResolverTestSuite;
use Surfnet\VerificationSuite\NameResolverTestSuite\NoInterfaceSuite;
use Surfnet\VerificationSuite\NameResolverTestSuite\Test\NoInterfaceTest;
use Surfnet\VerificationSuite\NameResolverTestSuite\Test\SomeTest;
use Surfnet\VerificationSuite\NameResolverTestSuite\Test\UnderScoredTestName;

class NameResolverTest extends UnitTest
{
    use DataProvider;

    /**
     * @test
     * @group EntityVerificationFramework
     * @group NameResolver
     */
    public function suite_classes_are_converted()
    {
        $suiteName = NameResolver::resolveToString(new NameResolverTestSuite());

        $this->assertEquals('name_resolver_test_suite', $suiteName);
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group NameResolver
     */
    public function test_classes_are_converted()
    {
        $someTestName = NameResolver::resolveToString(new SomeTest());
        $underScoredTestName = NameResolver::resolveToString(new UnderScoredTestName());

        $this->assertEquals('name_resolver_test_suite.some_test', $someTestName);
        $this->assertEquals('name_resolver_test_suite.under_scored_test_name', $underScoredTestName);
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group NameResolver
     * @dataProvider notObjectProvider
     *
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     * @expectedExceptionMessage is not a valid object
     */
    public function only_objects_can_be_converted_to_strings($value)
    {
        NameResolver::resolveToString($value);
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group NameResolver
     *
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException
     * @expectedExceptionMessage NameResolver may only be used for instances of VerificationTest or VerificationSuite
     */
    public function suites_not_implementing_verification_suite_cannot_be_converted()
    {
        NameResolver::resolveToString(new NoInterfaceSuite());
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group NameResolver
     *
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException
     * @expectedExceptionMessage NameResolver may only be used for instances of VerificationTest or VerificationSuite
     */
    public function test_not_implementing_verification_test_cannot_be_converted()
    {
        NameResolver::resolveToString(new NoInterfaceTest());
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group NameResolver
     */
    public function suite_names_are_converted_to_class()
    {
        $class = NameResolver::resolveToClass('name_resolver_test_suite');

        $this->assertEquals("Surfnet\\VerificationSuite\\NameResolverTestSuite", $class);
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group NameResolver
     */
    public function test_names_are_converted_to_class()
    {
        $testClass = NameResolver::resolveToClass('name_resolver_test_suite.some_test');
        $suiteClass = NameResolver::resolveToClass('name_resolver_test_suite.under_scored_test_name');

        $this->assertEquals("Surfnet\\VerificationSuite\\NameResolverTestSuite\\SomeTest", $testClass);
        $this->assertEquals("Surfnet\\VerificationSuite\\NameResolverTestSuite\\UnderScoredTestName", $suiteClass);
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group NameResolver
     * @dataProvider notNonEmptyOrBlankStringProvider
     *
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     */
    public function only_strings_can_be_converted_to_classes($value)
    {
        NameResolver::resolveToClass($value);
    }
}
