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
use Mockery\Matcher\Closure as ClosureMatcher;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ContactType;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\Validator;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;

class ContactTypeValidationTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_can_be_invalid()
    {
        /** @var ValidationContext|MockInterface $context */
        $context = m::mock(ValidationContext::class);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        $validator
            ->shouldReceive('addViolation')
            ->with(self::containsMatcher('Contact type must be one of "support", "administrative", "technical"'))
            ->once();

        $contactType = ContactType::fromString('invalid');
        $contactType->validate($validator, $context);
    }

    /**
     * @test
     * @group value
     */
    public function it_can_be_valid()
    {
        /** @var ValidationContext|MockInterface $context */
        $context = m::mock(ValidationContext::class);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        $validator->shouldReceive('addViolation')->never();

        $contactType = ContactType::fromString(ContactType::TYPE_SUPPORT);
        $contactType->validate($validator, $context);
    }

    /**
     * @param string $expectedToContain
     * @return ClosureMatcher
     */
    private static function containsMatcher($expectedToContain)
    {
        return m::on(function ($actual) use ($expectedToContain) {
            return strpos($actual, $expectedToContain) !== false;
        });
    }
}
