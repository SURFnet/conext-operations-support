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
use Surfnet\Conext\EntityVerificationFramework\Metadata\ApplicationUrl;
use Surfnet\Conext\EntityVerificationFramework\Metadata\AssertionConsumerServiceList;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ConfiguredMetadata;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ContactSet;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Description;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Keywords;
use Surfnet\Conext\EntityVerificationFramework\Metadata\LogoList;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Name;
use Surfnet\Conext\EntityVerificationFramework\Metadata\NameIdFormat;
use Surfnet\Conext\EntityVerificationFramework\Metadata\NameIdFormatList;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ShibbolethMetadataScopeList;
use Surfnet\Conext\EntityVerificationFramework\Metadata\SingleSignOnServiceList;
use Surfnet\Conext\EntityVerificationFramework\Metadata\SupportUrl;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;

class ConfiguredMetadataValidationTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     */
    public function sp_metadata_can_be_validated()
    {
        $acsList             = new AssertionConsumerServiceList();
        $name                = new Name();
        $description         = new Description();
        $contacts            = new ContactSet();
        $logos               = new LogoList();
        $defaultNameIdFormat = NameIdFormat::notSet();
        $shibmdScopeList     = new ShibbolethMetadataScopeList();
        $supportUrl          = new SupportUrl();
        $applicationUrl      = ApplicationUrl::fromString('https://app.invalid');

        $metadata = new ConfiguredMetadata(
            EntityType::SP(),
            $acsList,
            new SingleSignOnServiceList(),
            $defaultNameIdFormat,
            new NameIdFormatList(),
            $contacts,
            new Keywords(),
            $logos,
            $name,
            $description,
            $supportUrl,
            $applicationUrl,
            $shibmdScopeList
        );

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);
        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate')->with($acsList, $context)->once();
        $validator->shouldReceive('validate')->with($name, $context)->once();
        $validator->shouldReceive('validate')->with($description, $context)->once();
        $validator->shouldReceive('validate')->with($contacts, $context)->once();
        $validator->shouldReceive('validate')->with($logos, $context)->once();
        $validator->shouldReceive('validate')->with($defaultNameIdFormat, $context)->once();
        $validator->shouldReceive('validate')->with($shibmdScopeList, $context)->once();
        $validator->shouldReceive('validate')->with($supportUrl, $context)->once();
        $validator->shouldReceive('validate')->with($applicationUrl, $context)->once();
        $validator->shouldReceive('addViolation');

        $metadata->validate($validator, $context);
    }
    /**
     * @test
     * @group Metadata
     */
    public function idp_metadata_can_be_validated()
    {
        $name                = new Name();
        $description         = new Description();
        $contacts            = new ContactSet();
        $logos               = new LogoList();
        $defaultNameIdFormat = NameIdFormat::notSet();
        $shibmdScopeList     = new ShibbolethMetadataScopeList();
        $supportUrl          = new SupportUrl();
        $applicationUrl      = ApplicationUrl::fromString('https://app.invalid');

        $metadata   = new ConfiguredMetadata(
            EntityType::IdP(),
            new AssertionConsumerServiceList(),
            new SingleSignOnServiceList(),
            $defaultNameIdFormat,
            new NameIdFormatList(),
            $contacts,
            new Keywords(),
            $logos,
            $name,
            $description,
            $supportUrl,
            $applicationUrl,
            $shibmdScopeList
        );

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);
        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate')->with($name, $context)->once();
        $validator->shouldReceive('validate')->with($description, $context)->once();
        $validator->shouldReceive('validate')->with($contacts, $context)->once();
        $validator->shouldReceive('validate')->with($logos, $context)->once();
        $validator->shouldReceive('validate')->with($defaultNameIdFormat, $context)->once();
        $validator->shouldReceive('validate')->with($shibmdScopeList, $context)->once();
        $validator->shouldReceive('addViolation');

        $metadata->validate($validator, $context);
    }

    /**
     * @test
     * @group Metadata
     */
    public function it_requires_the_redirect_signing_option_to_be_configured()
    {
        $metadata = new ConfiguredMetadata(
            EntityType::SP(),
            new AssertionConsumerServiceList(),
            new SingleSignOnServiceList(),
            NameIdFormat::notSet(),
            new NameIdFormatList(),
            new ContactSet(),
            new Keywords(),
            new LogoList(),
            new Name(),
            new Description(),
            new SupportUrl(),
            ApplicationUrl::fromString(''),
            new ShibbolethMetadataScopeList()
        );

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate');
        $validator
            ->shouldReceive('addViolation')
            ->with('The sign redirects option is not configured to be enabled or disabled')
            ->once();

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        $metadata->validate($validator, $context);
    }

    /**
     * @test
     * @group Metadata
     */
    public function it_recognizes_the_redirect_signing_option_is_configured()
    {
        $metadata = new ConfiguredMetadata(
            EntityType::SP(),
            new AssertionConsumerServiceList(),
            new SingleSignOnServiceList(),
            NameIdFormat::notSet(),
            new NameIdFormatList(),
            new ContactSet(),
            new Keywords(),
            new LogoList(),
            new Name(),
            new Description(),
            new SupportUrl(),
            ApplicationUrl::fromString(''),
            new ShibbolethMetadataScopeList(),
            null,
            null,
            true
        );

        /** @var ConfiguredMetadataValidator|MockInterface $validator */
        $validator = m::mock(ConfiguredMetadataValidator::class);
        $validator->shouldReceive('validate');
        $validator->shouldReceive('addViolation')->never();

        /** @var ConfiguredMetadataValidationContext|MockInterface $context */
        $context = m::mock(ConfiguredMetadataValidationContext::class);

        $metadata->validate($validator, $context);
    }
}
