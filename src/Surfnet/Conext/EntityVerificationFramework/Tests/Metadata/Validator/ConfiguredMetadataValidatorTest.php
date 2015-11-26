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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Metadata\Validator;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\Validator;

class ConfiguredMetadataValidatorTest extends TestCase
{
    /**
     * @test
     * @group validation
     */
    public function it_delegates_validation_to_the_validatable()
    {
        /** @var MockInterface|ConfiguredMetadataValidationContext $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var MockInterface|ConfiguredMetadataValidatable $validatable */
        $validatable = m::mock(ConfiguredMetadataValidatable::class);
        $validatable
            ->shouldReceive('validate')
            ->with(
                m::on(function ($actual) use (&$validator) {
                    return $actual === $validator;
                }),
                $context
            )
            ->once();

        $validator = new Validator();
        $validator->validate($validatable, $context);
    }
}