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
use Surfnet\Conext\EntityVerificationFramework\Metadata\ContactSet;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ContactType;
use Surfnet\Conext\EntityVerificationFramework\Metadata\EmailAddress;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

class ContactSetValidationTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     */
    public function it_validates_its_contacts()
    {
        $contact0 = new Contact(ContactType::unknown(), EmailAddress::unknown());
        $contact1 = new Contact(ContactType::fromString(ContactType::TYPE_SUPPORT), EmailAddress::unknown());

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);
        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate')->with($contact0, $context)->once();
        $validator->shouldReceive('validate')->with($contact1, $context)->once();
        $validator->shouldReceive('addViolation')->never();

        $contacts = new ContactSet([$contact0, $contact1]);
        $contacts->validate($validator, $context);
    }
}
