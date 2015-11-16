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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Logo;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;

class ImageTest extends TestCase
{
    use DataProvider;

    /**
     * @test
     * @group value
     */
    public function it_can_be_deserialised()
    {
        Logo::deserialise(['url' => 'http://.png'], '');
        Logo::deserialise(['width' => '96'], '');
        Logo::deserialise(['height' => '72'], '');
    }

    /**
     * @test
     * @group value
     * @dataProvider nonStringProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $nonString
     */
    public function width_must_be_string($nonString)
    {
        Logo::deserialise(['width' => $nonString], '');
    }

    /**
     * @test
     * @group value
     * @dataProvider nonStringProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $nonString
     */
    public function height_must_be_string($nonString)
    {
        Logo::deserialise(['height' => $nonString], '');
    }

    /**
     * @test
     * @group value
     * @dataProvider invalidImageSizes
     *
     * @param mixed $invalidImageSize
     */
    public function width_can_be_invalid($invalidImageSize)
    {
        $this->assertFalse(Logo::deserialise(['width' => $invalidImageSize], '')->isValid());
    }

    /**
     * @test
     * @group value
     * @dataProvider invalidImageSizes
     *
     * @param mixed $invalidImageSize
     */
    public function height_can_be_invalid($invalidImageSize)
    {
        $this->assertFalse(Logo::deserialise(['height' => $invalidImageSize], '')->isValid());
    }

    public function invalidImageSizes()
    {
        return [
            'empty string' => [''],
            'blank string' => [' '],
            'floaty string' => ['1.1'],
            'floaty string' => ['0.0'],
            'negative integer string' => ['-1'],
            'word' => ['word'],
        ];
    }
}
