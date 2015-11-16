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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\NameIdFormat;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;

class NameIdFormatTest extends TestCase
{
    use DataProvider;

    /**
     * @test
     * @group value
     */
    public function it_can_be_created()
    {
        NameIdFormat::fromUrn('urn:mad:bro');
    }

    /**
     * @test
     * @group value
     * @dataProvider nonStringProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $nonString
     */
    public function it_only_accepts_strings($nonString)
    {
        NameIdFormat::fromUrn($nonString);
    }

    /**
     * @test
     * @group value
     */
    public function it_can_equal_other_formats()
    {
        $format0 = NameIdFormat::fromUrn('urn:mad:bro');
        $format1 = NameIdFormat::fromUrn('urn:mad:bro');

        $this->assertTrue($format0->equals($format1));
    }

    /**
     * @test
     * @group value
     */
    public function it_can_not_equal_other_formats()
    {
        $format0 = NameIdFormat::fromUrn('urn:mad:bro');
        $format1 = NameIdFormat::fromUrn('urn:mad:bra');

        $this->assertFalse($format0->equals($format1));
    }
}
