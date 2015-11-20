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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Logo;
use Surfnet\Conext\EntityVerificationFramework\Metadata\LogoList;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadataValidatorInterface;

class LogoListValidationTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_validates_its_logos()
    {
        $logo0 = Logo::deserialise([], 'propPath');
        $logo1 = Logo::deserialise([], 'propPath');

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);
        /** @var ConfiguredMetadataValidatorInterface|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidatorInterface::class);
        $validator->shouldReceive('validate')->with($logo0, $context)->once();
        $validator->shouldReceive('validate')->with($logo1, $context)->once();
        $validator->shouldReceive('addViolation')->never();

        $logos = new LogoList([$logo0, $logo1]);
        $logos->validate($validator, $context);
    }
}
