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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Contact;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ContactType;
use Surfnet\Conext\EntityVerificationFramework\Metadata\EmailAddress;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;

class ContactValidationTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     */
    public function it_validates_its_type_and_email()
    {
        $contactType  = ContactType::notSet();
        $emailAddress = EmailAddress::notSet();

        /** @var MockInterface|ConfiguredMetadataConstraintViolationWriter $violations */
        $violations = m::mock(ConfiguredMetadataConstraintViolationWriter::class);
        $violations->shouldReceive('add')->never();

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var MockInterface|ConfiguredMetadataVisitor $visitor */
        $visitor = m::mock(ConfiguredMetadataVisitor::class);
        $anyViolationWriter = m::type(ConfiguredMetadataConstraintViolationWriter::class);
        $visitor->shouldReceive('visit')->with($contactType, $anyViolationWriter, $context)->once();
        $visitor->shouldReceive('visit')->with($emailAddress, $anyViolationWriter, $context)->once();

        $contact = new Contact($contactType, $emailAddress, 'Given name', 'Surname');
        $contact->validate($visitor, $violations, $context);
    }

    /**
     * @test
     * @group Metadata
     */
    public function it_validates_its_given_name_and_surname()
    {
        /** @var MockInterface|ConfiguredMetadataConstraintViolationWriter $violations */
        $violations = m::mock(ConfiguredMetadataConstraintViolationWriter::class);
        $violations->shouldReceive('add')->with('Contact given name is not configured or empty')->once();
        $violations->shouldReceive('add')->with('Contact surname is not configured or empty')->once();

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        /** @var MockInterface|ConfiguredMetadataVisitor $visitor */
        $visitor = m::mock(ConfiguredMetadataVisitor::class);
        $visitor->shouldReceive('visit');

        $contact = new Contact(ContactType::notSet(), EmailAddress::notSet(), ' ', null);
        $contact->validate($visitor, $violations, $context);
    }
}
