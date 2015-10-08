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
use Surfnet\Conext\EntityVerificationFramework\Value\EntityKeywords;

final class EntityKeywordsTest extends TestCase
{
    use DataProvider;

    /**
     * @test
     * @group value
     */
    public function it_deserialises_keywords()
    {
        EntityKeywords::deserialise(['nl' => 'meh', 'en' => 'earth wind fire'], '');
    }

    /**
     * @test
     * @group value
     * @dataProvider nonStringProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $nonString
     */
    public function it_doesnt_accept_non_strings_as_keyword($nonString)
    {
        EntityKeywords::deserialise(['nl' => $nonString, 'en' => 'that movie with the robot and some wonky kid'], '');
    }

    /**
     * @test
     * @group value
     */
    public function two_keywords_can_equal_each_other()
    {
        $keyword0 = EntityKeywords::deserialise(['nl' => 'meh', 'en' => 'varokaa php on tyhmän unelma'], '');
        $keyword1 = EntityKeywords::deserialise(['en' => 'varokaa php on tyhmän unelma', 'nl' => 'meh'], '');

        $this->assertTrue($keyword0->equals($keyword1));
    }

    /**
     * @test
     * @group value
     */
    public function two_keywords_can_differ()
    {
        $keyword0 = EntityKeywords::deserialise(['nl' => 'meh', 'en' => 'gregor samsa woke from his troubled dreams'], '');
        $keyword1 = EntityKeywords::deserialise(['en' => 'he liikkuivat yhtenä', 'nl' => 'meh'], '');

        $this->assertFalse($keyword0->equals($keyword1));
    }

    /**
     * @test
     * @group value
     * @dataProvider intFloatBoolProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $key
     */
    public function it_doesnt_accept_non_strings_as_locale($key)
    {
        EntityKeywords::deserialise([$key => 'meh', 'en' => 'https://domain.invalid'], '');
    }
}
