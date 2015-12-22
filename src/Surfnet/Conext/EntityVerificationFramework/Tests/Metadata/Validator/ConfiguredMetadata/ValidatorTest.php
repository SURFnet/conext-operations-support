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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Metadata\Validator\ConfiguredMetadata;

use Mockery as m;
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationReader;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\Validator;

class ValidatorTest extends TestCase
{
    /**
     * @test
     * @group Validator
     */
    public function validators_visit_their_validatables()
    {
        /** @var MockInterface|ConfiguredMetadataValidationContext $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);;

        /** @var MockInterface|ConfiguredMetadataValidatable $validatable */
        $validatable = m::mock(ConfiguredMetadataValidatable::class);
        $validatable->shouldReceive('validate')->once()->with(
            m::type(ConfiguredMetadataVisitor::class),
            m::type(ConfiguredMetadataConstraintViolationWriter::class),
            $context
        );

        $validator = new Validator();
        $validator->validate($validatable, $context);
    }

    /**
     * @test
     * @group Validator
     */
    public function validators_return_added_violations_in_a_list()
    {
        /** @var MockInterface|ConfiguredMetadataValidationContext $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);;

        /** @var MockInterface|ConfiguredMetadataValidatable $validatable */
        $validatable = m::mock(ConfiguredMetadataValidatable::class);
        $validatable
            ->shouldReceive('validate')
            ->andReturnUsing(
                function ($visitor, ConfiguredMetadataConstraintViolationWriter $violations, $context) {
                    $violations->add('Error');
                }
            );

        $validator  = new Validator();
        $violations = $validator->validate($validatable, $context);

        $this->assertInstanceOf(ConfiguredMetadataConstraintViolationReader::class, $violations);
        $this->assertEquals(['Error'], $violations->all());
    }
}
