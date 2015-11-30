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

namespace Surfnet\Conext\EntityVerificationFramework\Metadata;

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\SubpathValidator;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ConfiguredMetadata implements ConfiguredMetadataValidatable
{
    /** @var EntityType */
    private $entityType;
    /** @var Url|null */
    private $publishedMetadataUrl;
    /** @var AssertionConsumerServiceList */
    private $assertionConsumerServices;
    /** @var SingleSignOnServiceList */
    private $singleSignOnServices;
    /** @var ContactSet */
    private $contacts;
    /** @var Name */
    private $name;
    /** @var Description */
    private $description;
    /** @var LogoList */
    private $logos;
    /** @var boolean|null */
    private $signRedirects;
    /** @var MultiLocaleUrl */
    private $url;
    /** @var Keywords */
    private $keywords;
    /** @var NameIdFormat */
    private $defaultNameIdFormat;
    /** @var NameIdFormatList */
    private $acceptableNameIdFormats;
    /** @var ShibmdScopeList */
    private $scopes;
    /** @var PemEncodedX509Certificate|null */
    private $certData;
    /** @var GuestQualifier|null */
    private $guestQualifier;
    /** @var mixed[] Array indexed by string keys */
    private $freeformProperties = [];

    /**
     * @param EntityType                     $entityType
     * @param AssertionConsumerServiceList   $assertionConsumerServices
     * @param SingleSignOnServiceList        $singleSignOnServices
     * @param NameIdFormat                   $defaultNameIdFormat
     * @param NameIdFormatList               $acceptableNameIdFormats
     * @param ContactSet                     $contacts
     * @param Keywords                       $keywords
     * @param LogoList                       $logos
     * @param Name                           $name
     * @param Description                    $description
     * @param MultiLocaleUrl                 $url
     * @param ShibmdScopeList                $scopes
     * @param null|Url                       $publishedMetadataUrl
     * @param null|PemEncodedX509Certificate $certData
     * @param bool|null                      $signRedirects
     * @param GuestQualifier|null            $guestQualifier
     * @param mixed[]                        $freeformProperties
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityType $entityType,
        AssertionConsumerServiceList $assertionConsumerServices,
        SingleSignOnServiceList $singleSignOnServices,
        NameIdFormat $defaultNameIdFormat,
        NameIdFormatList $acceptableNameIdFormats,
        ContactSet $contacts,
        Keywords $keywords,
        LogoList $logos,
        Name $name,
        Description $description,
        MultiLocaleUrl $url,
        ShibmdScopeList $scopes,
        Url $publishedMetadataUrl = null,
        PemEncodedX509Certificate $certData = null,
        $signRedirects = null,
        GuestQualifier $guestQualifier = null,
        array $freeformProperties = []
    ) {
        if ($signRedirects !== null) {
            Assert::boolean($signRedirects, null, 'signRedirects');
        }

        $this->entityType                = $entityType;
        $this->publishedMetadataUrl      = $publishedMetadataUrl;
        $this->assertionConsumerServices = $assertionConsumerServices;
        $this->singleSignOnServices      = $singleSignOnServices;
        $this->contacts                  = $contacts;
        $this->name                      = $name;
        $this->description               = $description;
        $this->logos                     = $logos;
        $this->signRedirects             = $signRedirects;
        $this->url                       = $url;
        $this->scopes                    = $scopes;
        $this->keywords                  = $keywords;
        $this->defaultNameIdFormat       = $defaultNameIdFormat;
        $this->acceptableNameIdFormats   = $acceptableNameIdFormats;
        $this->certData                  = $certData;
        $this->guestQualifier            = $guestQualifier;
        $this->freeformProperties        = $freeformProperties;
    }

    public function validate(
        ConfiguredMetadataValidator $validator,
        ConfiguredMetadataValidationContext $context
    ) {
        $validator->validate($this->name, $context);
        $validator->validate($this->description, $context);
        $validator->validate($this->contacts, $context);
        $validator->validate($this->logos, $context);
        (new SubpathValidator($validator, 'Default NameIDFormat'))->validate($this->defaultNameIdFormat, $context);
        $validator->validate($this->scopes, $context);

        if ($this->signRedirects === null) {
            $validator->addViolation('The sign redirects option is not configured to be enabled or disabled');
        }
    }

    /**
     * @return EntityType
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * @return bool
     */
    public function hasPublishedMetadataUrl()
    {
        return $this->publishedMetadataUrl !== null;
    }

    /**
     * @return Url
     */
    public function getPublishedMetadataUrl()
    {
        if ($this->publishedMetadataUrl === null) {
            throw new LogicException('Published metadata URL is not known');
        }

        return $this->publishedMetadataUrl;
    }
}
