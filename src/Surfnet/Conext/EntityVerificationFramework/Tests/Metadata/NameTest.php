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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Name;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

class NameTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     * @dataProvider namesAndTheirViolations
     *
     * @param Name $name
     * @param string[] $violations
     */
    public function it_reports_a_violation_when_a_locale_is_not_filled(Name $name, $violations)
    {
        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        foreach ($violations as $violation) {
            $validator
                ->shouldReceive('addViolation')
                ->with($violation)
                ->once();
        }

        $name->validate($validator, $context);
    }

    public function namesAndTheirViolations()
    {
        return [
            'no locales' => [
                new Name(),
                ['No English name configured', 'No Dutch name configured']
            ],
            'no Dutch locale' => [
                new Name(['en' => 'Yeah']),
                ['No Dutch name configured']
            ],
            'no English locale' => [
                new Name(['nl' => 'Yeah']),
                ['No English name configured']
            ],
            'all locales' => [
                new Name(['nl' => 'Oh', 'en' => 'yeah']),
                []
            ],
        ];
    }
}
