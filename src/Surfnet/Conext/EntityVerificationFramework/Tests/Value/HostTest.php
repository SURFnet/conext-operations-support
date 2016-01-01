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

use PHPUnit_Framework_TestCase as UnitTest;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;
use Surfnet\Conext\EntityVerificationFramework\Value\Host;

class HostTest extends UnitTest
{
    use DataProvider;

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Value
     *
     * @dataProvider notNonEmptyOrBlankStringProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $invalidValue
     */
    public function only_non_empty_strings_are_valid_hosts($invalidValue)
    {
        new Host($invalidValue, 80);
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Value
     */
    public function the_same_hosts_are_considered_equal()
    {
        $base      = new Host('a', 80);
        $theSame   = new Host('a', 80);
        $different = new Host('A', 80);
        $otherPort = new Host('A', 443);

        $this->assertTrue($base->equals($theSame));
        $this->assertFalse($base->equals($different));
        $this->assertFalse($different->equals($otherPort));
    }
}
