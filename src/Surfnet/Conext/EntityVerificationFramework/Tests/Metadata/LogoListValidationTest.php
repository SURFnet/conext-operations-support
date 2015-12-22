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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;

class LogoListValidationTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     */
    public function it_validates_its_logos()
    {
        $logo0 = Logo::deserialize([], 'propPath');
        $logo1 = Logo::deserialize([], 'propPath');

        /** @var MockInterface|ConfiguredMetadataConstraintViolationWriter $violations */
        $violations = m::mock(ConfiguredMetadataConstraintViolationWriter::class);
        $violations->shouldReceive('add')->never();

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var MockInterface|ConfiguredMetadataVisitor $visitor */
        $visitor = m::mock(ConfiguredMetadataVisitor::class);
        $anyViolationWriter = m::type(ConfiguredMetadataConstraintViolationWriter::class);
        $visitor->shouldReceive('visit')->with($logo0, $anyViolationWriter, $context)->once();
        $visitor->shouldReceive('visit')->with($logo1, $anyViolationWriter, $context)->once();

        $logos = new LogoList([$logo0, $logo1]);
        $logos->validate($visitor, $violations, $context);
    }
}
