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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadataValidatorInterface;

class ContactValidationTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_validates_its_type_and_email()
    {
        $contactType  = ContactType::unknown();
        $emailAddress = EmailAddress::unknown();

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);
        /** @var ConfiguredMetadataValidatorInterface|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidatorInterface::class);
        $validator->shouldReceive('validate')->with($contactType, $context)->once();
        $validator->shouldReceive('validate')->with($emailAddress, $context)->once();
        $validator->shouldReceive('addViolation')->never();

        $contact = new Contact($contactType, $emailAddress, 'Given name', 'Surname');
        $contact->validate($validator, $context);
    }

    /**
     * @test
     * @group value
     */
    public function it_validates_its_given_name_and_surname()
    {
        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);
        /** @var ConfiguredMetadataValidatorInterface|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidatorInterface::class);
        $validator->shouldReceive('validate');
        $validator->shouldReceive('addViolation')->with('Contact given name is not configured or empty')->once();
        $validator->shouldReceive('addViolation')->with('Contact surname is not configured or empty')->once();

        $contact = new Contact(ContactType::unknown(), EmailAddress::unknown(), ' ', null);
        $contact->validate($validator, $context);
    }
}
