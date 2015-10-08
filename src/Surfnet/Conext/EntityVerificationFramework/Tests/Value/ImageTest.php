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
use Surfnet\Conext\EntityVerificationFramework\Value\Image;

class ImageTest extends TestCase
{
    use DataProvider;

    /**
     * @test
     * @group value
     */
    public function it_can_be_deserialised()
    {
        Image::deserialise(['url' => 'http://.png'], '');
        Image::deserialise(['width' => '96'], '');
        Image::deserialise(['height' => '72'], '');
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
        Image::deserialise(['width' => $nonString], '');
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
        Image::deserialise(['height' => $nonString], '');
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
        $this->assertFalse(Image::deserialise(['width' => $invalidImageSize], '')->isValid());
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
        $this->assertFalse(Image::deserialise(['height' => $invalidImageSize], '')->isValid());
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
