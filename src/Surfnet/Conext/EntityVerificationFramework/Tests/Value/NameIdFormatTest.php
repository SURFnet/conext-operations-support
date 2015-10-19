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

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;
use Surfnet\Conext\EntityVerificationFramework\Value\NameIdFormat;

class NameIdFormatTest extends TestCase
{
    use DataProvider;

    /**
     * @test
     * @group value
     */
    public function it_can_be_created()
    {
        new NameIdFormat('urn:mad:bro');
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
        new NameIdFormat($nonString);
    }

    /**
     * @test
     * @group value
     */
    public function it_can_equal_other_formats()
    {
        $format0 = new NameIdFormat('urn:mad:bro');
        $format1 = new NameIdFormat('urn:mad:bro');

        $this->assertTrue($format0->equals($format1));
    }

    /**
     * @test
     * @group value
     */
    public function it_can_not_equal_other_formats()
    {
        $format0 = new NameIdFormat('urn:mad:bro');
        $format1 = new NameIdFormat('urn:mad:bra');

        $this->assertFalse($format0->equals($format1));
    }

    /**
     * @test
     * @group value
     */
    public function its_format_can_be_validated()
    {
        $this->assertFalse((new NameIdFormat('urn:mad:bro'))->isValidFormat());
        $this->assertFalse((new NameIdFormat('urn:oasis:names:tc:SAML:2.0:nameid-format:hoedje-van-hoedje-van'))->isValidFormat());

        $this->assertTrue((new NameIdFormat(NameIdFormat::FORMAT_SAML_20_PERSISTENT))->isValidFormat());
        $this->assertTrue((new NameIdFormat(NameIdFormat::FORMAT_SAML_20_TRANSIENT))->isValidFormat());
    }
}
