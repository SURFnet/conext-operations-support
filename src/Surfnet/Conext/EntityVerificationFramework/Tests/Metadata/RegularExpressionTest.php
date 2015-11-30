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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

final class RegularExpressionTest extends TestCase
{
    /**
     * @test
     * @group Value
     * @dataProvider invalidRegularExpressions
     *
     * @param string $pattern
     * @param array $errorMessages
     */
    public function regular_expressions_can_be_invalid($pattern, array $errorMessages)
    {
        /** @var MockInterface|ConfiguredMetadataValidationContext $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);
        /** @var MockInterface|ConfiguredMetadataValidator $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);

        foreach ($errorMessages as $errorMessage) {
            $validator->shouldReceive('addViolation')->once()->with($errorMessage);
        }

        $regex = new RegularExpression($pattern);
        $regex->validate($validator, $context);
    }

    public function invalidRegularExpressions()
    {
        return [
            "Delimiters don't match"   => [
                '|pattern/',
                ["preg_match(): No ending delimiter '|' found"],
            ],
            'Missing ending delimiter' => [
                '|pattern',
                ["preg_match(): No ending delimiter '|' found"],
            ],
            'No delimiters'            => [
                'z',
                ['preg_match(): Delimiter must not be alphanumeric or backslash'],
            ],
        ];
    }
}
