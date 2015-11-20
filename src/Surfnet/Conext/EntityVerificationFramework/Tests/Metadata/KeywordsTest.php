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
use Mockery as m;
use Mockery\MockInterface;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Keywords;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\Validator;

class KeywordsTest extends TestCase
{
    /**
     * @test
     * @group value
     * @dataProvider keywordsesAndTheirViolations
     *
     * @param Keywords $keywords
     * @param string[] $violations
     */
    public function it_reports_a_violation_when_a_locale_is_not_filled(Keywords $keywords, $violations)
    {
        /** @var ValidationContext|MockInterface $context */
        $context = m::mock(ValidationContext::class);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        foreach ($violations as $violation) {
            $validator
                ->shouldReceive('addViolation')
                ->with($violation)
                ->once();
        }

        $keywords->validate($validator, $context);
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
