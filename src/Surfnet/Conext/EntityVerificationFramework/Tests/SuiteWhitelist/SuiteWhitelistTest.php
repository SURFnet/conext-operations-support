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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\SuiteWhitelist;

use Mockery as m;
use PHPUnit_Framework_TestCase as UnitTest;
use Surfnet\Conext\EntityVerificationFramework\SuiteWhitelist\SuiteWhitelist;

class WhitelistTest extends UnitTest
{
    /**
     * @test
     * @group Whitelist
     */
    public function contains_only_given_suites()
    {
        $whitelist = new SuiteWhitelist(['first_verification_suite', 'second_verification_suite']);

        $this->assertTrue(
            $whitelist->contains('first_verification_suite')
        );
        $this->assertTrue(
            $whitelist->contains('second_verification_suite')
        );
        $this->assertFalse(
            $whitelist->contains('third_verification_suite')
        );
    }
}
