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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Description;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

class DescriptionTest extends TestCase
{
    /**
     * @test
     * @group value
     * @dataProvider descriptionsAndTheirViolations
     *
     * @param Description $description
     * @param string[] $violations
     */
    public function it_reports_a_violation_when_a_locale_is_not_filled(Description $description, $violations)
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

        $description->validate($validator, $context);
    }

    public function descriptionsAndTheirViolations()
    {
        return [
            'no locales' => [
                new Description(),
                ['No English description configured', 'No Dutch description configured']
            ],
            'no Dutch locale' => [
                new Description(['en' => 'Yeah']),
                ['No Dutch description configured']
            ],
            'no English locale' => [
                new Description(['nl' => 'Yeah']),
                ['No English description configured']
            ],
            'all locales' => [
                new Description(['nl' => 'Oh', 'en' => 'yeah']),
                []
            ],
        ];
    }
}
