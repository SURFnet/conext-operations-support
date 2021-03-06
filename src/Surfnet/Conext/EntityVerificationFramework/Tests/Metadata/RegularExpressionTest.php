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

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\RegularExpression;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;

final class RegularExpressionTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     * @dataProvider invalidRegularExpressions
     *
     * @param string $pattern
     * @param array $errorMessages
     */
    public function regular_expressions_can_be_invalid($pattern, array $errorMessages)
    {
        /** @var MockInterface|ConfiguredMetadataConstraintViolationWriter $violations */
        $violations = m::mock(ConfiguredMetadataConstraintViolationWriter::class);
        foreach ($errorMessages as $errorMessage) {
            $violations->shouldReceive('add')->once()->with($errorMessage);
        }

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var MockInterface|ConfiguredMetadataVisitor $visitor */
        $visitor = m::mock(ConfiguredMetadataVisitor::class);

        $regex = new RegularExpression($pattern);
        $regex->validate($visitor, $violations, $context);
    }

    public function invalidRegularExpressions()
    {
        return [
            "Delimiters don't match"   => [
                '|pattern/',
                [
                    'An error would occur during execution of regular expression "|pattern/": ' .
                    '"preg_match(): No ending delimiter \'|\' found"',
                    'Regular expression would not execute: it is somehow invalid',
                ],
            ],
            'Missing ending delimiter' => [
                '|pattern',
                [
                    'An error would occur during execution of regular expression "|pattern": ' .
                    '"preg_match(): No ending delimiter \'|\' found"',
                    'Regular expression would not execute: it is somehow invalid',
                ],
            ],
            'No delimiters'            => [
                'z',
                [
                    'An error would occur during execution of regular expression "z": ' .
                    '"preg_match(): Delimiter must not be alphanumeric or backslash"',
                    'Regular expression would not execute: it is somehow invalid',
                ],
            ],
        ];
    }

    /**
     * @test
     * @group Metadata
     */
    public function regular_expressions_can_be_equal()
    {
        $this->assertTrue(
            (new RegularExpression('~33~'))->equals(new RegularExpression('~33~')),
            'Expected regular expressions to equal each other'
        );
        $this->assertTrue(
            (new RegularExpression(''))->equals(new RegularExpression('')),
            'Expected regular expressions to equal each other'
        );
    }

    /**
     * @test
     * @group Metadata
     */
    public function regular_expressions_can_be_unequal()
    {
        $this->assertFalse(
            (new RegularExpression('~abc~'))->equals(new RegularExpression('~33~')),
            'Expected regular expressions to not equal each other'
        );
        $this->assertFalse(
            (new RegularExpression('~abc~'))->equals(new RegularExpression('/abc/')),
            'Expected regular expressions to not equal each other'
        );
        $this->assertFalse(
            (new RegularExpression(''))->equals(new RegularExpression('/abc/')),
            'Expected regular expressions to not equal each other'
        );
    }
}
