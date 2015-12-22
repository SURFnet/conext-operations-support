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
use Surfnet\Conext\EntityVerificationFramework\Metadata\MultiLocaleUrl;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;

final class MultiLocaleUrlTest extends TestCase
{
    use DataProvider;

    /**
     * @test
     * @group Metadata
     */
    public function it_deserializes_urls()
    {
        MultiLocaleUrl::deserialize(['nl' => 'meh', 'en' => 'https://domain.invalid'], '');
    }

    /**
     * @test
     * @group Metadata
     * @dataProvider nonStringProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $nonString
     */
    public function it_doesnt_accept_non_strings_as_url($nonString)
    {
        MultiLocaleUrl::deserialize(['nl' => $nonString, 'en' => 'https://domain.invalid'], '');
    }

    /**
     * @test
     * @group Metadata
     */
    public function it_can_test_whether_it_contains_a_valid_url_for_a_locale()
    {
        $url = MultiLocaleUrl::deserialize(['nl' => 'meh', 'en' => 'https://domain.invalid'], '');

        $this->assertFalse($url->hasValidUrlForLocale('de'));
        $this->assertFalse($url->hasValidUrlForLocale('nl'));
        $this->assertTrue($url->hasValidUrlForLocale('en'));
    }

    /**
     * @test
     * @group Metadata
     */
    public function it_is_valid_when_all_its_urls_are_valid()
    {
        $url = MultiLocaleUrl::deserialize(['nl' => 'ftp://leech-access.invalid:21', 'en' => 'https://domain.invalid'], '');
        $this->assertTrue($url->isValid());
    }

    /**
     * @test
     * @group Metadata
     */
    public function it_is_invalid_when_some_of_its_urls_are_invalid()
    {
        $url = MultiLocaleUrl::deserialize(['nl' => 'meh', 'en' => 'https://domain.invalid'], '');
        $this->assertFalse($url->isValid());
    }

    /**
     * @test
     * @group Metadata
     */
    public function two_urls_can_equal_each_other()
    {
        $url0 = MultiLocaleUrl::deserialize(['nl' => 'meh', 'en' => 'https://domain.invalid'], '');
        $url1 = MultiLocaleUrl::deserialize(['en' => 'https://domain.invalid', 'nl' => 'meh'], '');

        $this->assertTrue($url0->equals($url1));
    }

    /**
     * @test
     * @group Metadata
     */
    public function two_urls_can_differ()
    {
        $url0 = MultiLocaleUrl::deserialize(['nl' => 'meh', 'en' => 'https://domain.invalid'], '');
        $url1 = MultiLocaleUrl::deserialize(['nl' => 'meh'], '');

        $this->assertFalse($url0->equals($url1));
    }

    /**
     * @test
     * @group Metadata
     * @dataProvider intFloatBoolProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $key
     */
    public function it_doesnt_accept_non_strings_as_locale($key)
    {
        MultiLocaleUrl::deserialize([$key => 'meh', 'en' => 'https://domain.invalid'], '');
    }
}
