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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\SubpathValidator;

class SubpathConfiguredMetadataValidatorTest extends TestCase
{
    /**
     * @test
     * @group validation
     */
    public function it_prepends_the_subpath_to_added_violations()
    {
        /** @var MockInterface|ConfiguredMetadataValidator $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('addViolation')->with("Prepended: but it's wrong!")->once();

        $subpathValidator = new SubpathValidator($validator, 'Prepended');
        $subpathValidator->addViolation("but it's wrong!");
    }
}