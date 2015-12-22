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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Keywords;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;

class KeywordsTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     * @dataProvider keywordsesAndTheirViolations
     *
     * @param Keywords $keywords
     * @param string[] $violationMessages
     */
    public function it_reports_a_violation_when_a_locale_is_not_filled(Keywords $keywords, $violationMessages)
    {
        /** @var MockInterface|ConfiguredMetadataConstraintViolationWriter $violations */
        $violations = m::mock(ConfiguredMetadataConstraintViolationWriter::class);
        foreach ($violationMessages as $violationMessage) {
            $violations
                ->shouldReceive('add')
                ->with($violationMessage)
                ->once();
        }

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var MockInterface|ConfiguredMetadataVisitor $visitor */
        $visitor = m::mock(ConfiguredMetadataVisitor::class);

        $keywords->validate($visitor, $violations, $context);
    }

    public function keywordsesAndTheirViolations()
    {
        return [
            'no locales' => [
                new Keywords(),
                ['No English keywords configured', 'No Dutch keywords configured']
            ],
            'no Dutch locale' => [
                new Keywords(['en' => 'Yeah']),
                ['No Dutch keywords configured']
            ],
            'no English locale' => [
                new Keywords(['nl' => 'Yeah']),
                ['No English keywords configured']
            ],
            'all locales' => [
                new Keywords(['nl' => 'Oh', 'en' => 'yeah']),
                []
            ],
        ];
    }
}
