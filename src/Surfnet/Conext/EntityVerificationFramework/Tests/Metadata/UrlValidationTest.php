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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\Validator;

class UrlValidationTest extends TestCase
{
    /**
     * @test
     * @group validation
     */
    public function a_violation_is_reported_when_the_url_is_invalid()
    {
        /** @var MockInterface|ValidationContext $context */
        $context = m::mock(ValidationContext::class);
        /** @var MockInterface|Validator $validator */
        $validator = m::mock(Validator::class);
        $validator->shouldReceive('addViolation')->with('URL "###" is not valid')->once();

        $url = Url::fromString('###');
        $url->validate($validator, $context);
    }

    /**
     * @test
     * @group validation
     */
    public function no_violations_are_reported_when_the_url_is_valid()
    {
        /** @var MockInterface|ValidationContext $context */
        $context = m::mock(ValidationContext::class);
        /** @var MockInterface|Validator $validator */
        $validator = m::mock(Validator::class);
        $validator->shouldReceive('addViolation')->never();

        $url = Url::fromString('https://surfc0next.invalid');
        $url->validate($validator, $context);
    }
}
