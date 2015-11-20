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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Description;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\Validator;

class DescriptionTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_reports_violations_when_locales_en_andor_nl_are_not_filled()
    {
        /** @var ValidationContext|MockInterface $context */
        $context = m::mock(ValidationContext::class);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        $validator
            ->shouldReceive('addViolation')
            ->with('No English description configured')
            ->once();
        $validator
            ->shouldReceive('addViolation')
            ->with('No Dutch description configured')
            ->once();

        $description = new Description();
        $description->validate($validator, $context);
    }

    /**
     * @test
     * @group value
     */
    public function it_verifies_locales_en_and_nl_are_filled()
    {
        /** @var ValidationContext|MockInterface $context */
        $context = m::mock(ValidationContext::class);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        $validator->shouldReceive('addViolation')->never();

        $description = new Description(['en' => 'English', 'nl' => 'Dutch']);
        $description->validate($validator, $context);
    }
}
