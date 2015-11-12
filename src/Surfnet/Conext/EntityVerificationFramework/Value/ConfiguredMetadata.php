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

namespace Surfnet\Conext\EntityVerificationFramework\Value;

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ConfiguredMetadata
{
    /** @var EntityType */
    private $entityType;
    /** @var Url|null */
    private $publishedMetadataUrl;
    /** @var AssertionConsumerServiceList */
    private $assertionConsumerServices = [];
    /** @var SingleSignOnServiceList */
    private $singleSignOnServices = [];
    /** @var ContactSet */
    private $contacts;
    /** @var MultiLocaleString|null */
    private $name;
    /** @var MultiLocaleString|null */
    private $description;
    /** @var ImageList */
    private $logos;
    /** @var boolean|null */
    private $signRedirects;
    /** @var MultiLocaleUrl|null */
    private $url;
    /** @var MultiLocaleString|null */
    private $keywords;
    /** @var NameIdFormat|null */
    private $defaultNameIdFormat;
    /** @var NameIdFormatList */
    private $acceptableNameIdFormats;
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
     * @param NameIdFormatList               $acceptableNameIdFormats
     * @param ContactSet                     $contacts
     * @param MultiLocaleString              $keywords
     * @param ImageList                      $logos
     * @param null|MultiLocaleString         $name
     * @param null|MultiLocaleString         $description
     * @param null|Url                       $publishedMetadataUrl
     * @param null|PemEncodedX509Certificate $certData
     * @param null|NameIdFormat              $defaultNameIdFormat
     * @param null|MultiLocaleUrl            $url
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
        NameIdFormatList $acceptableNameIdFormats,
        ContactSet $contacts,
        MultiLocaleString $keywords,
        ImageList $logos,
        MultiLocaleString $name = null,
        MultiLocaleString $description = null,
        Url $publishedMetadataUrl = null,
        PemEncodedX509Certificate $certData = null,
        NameIdFormat $defaultNameIdFormat = null,
        MultiLocaleUrl $url = null,
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
        $this->keywords                  = $keywords;
        $this->defaultNameIdFormat       = $defaultNameIdFormat;
        $this->acceptableNameIdFormats   = $acceptableNameIdFormats;
        $this->certData                  = $certData;
        $this->guestQualifier            = $guestQualifier;
        $this->freeformProperties        = $freeformProperties;
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

    /**
     * @return AssertionConsumerServiceList
     */
    public function getAssertionConsumerServices()
    {
        return $this->assertionConsumerServices;
    }

    /**
     * @return SingleSignOnServiceList
     */
    public function getSingleSignOnServices()
    {
        return $this->singleSignOnServices;
    }

    /**
     * @return ContactSet
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * @return bool
     */
    public function hasName()
    {
        return $this->name !== null;
    }

    /**
     * @return MultiLocaleString
     */
    public function getName()
    {
        if ($this->name === null) {
            throw new LogicException('Name is not known');
        }

        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasDescription()
    {
        return $this->description !== null;
    }

    /**
     * @return MultiLocaleString
     */
    public function getDescription()
    {
        if ($this->description === null) {
            throw new LogicException('Description is not known');
        }

        return $this->description;
    }

    /**
     * @return ImageList
     */
    public function getLogos()
    {
        return $this->logos;
    }

    /**
     * @return bool
     */
    public function hasSignRedirectsConfigured()
    {
        return $this->signRedirects !== null;
    }

    /**
     * @return bool
     */
    public function mustRedirectResponsesBeSigned()
    {
        if ($this->signRedirects === null) {
            throw new LogicException('It is unknown whether redirect responses must be signed');
        }

        return $this->signRedirects;
    }

    /**
     * @return bool
     */
    public function hasUrl()
    {
        return $this->url !== null;
    }

    /**
     * @return MultiLocaleUrl
     */
    public function getUrl()
    {
        if ($this->url === null) {
            throw new LogicException('URL is not known');
        }

        return $this->url;
    }

    /**
     * @return bool
     */
    public function hasKeywords()
    {
        return $this->keywords !== null;
    }

    /**
     * @return MultiLocaleString
     */
    public function getKeywords()
    {
        if ($this->keywords === null) {
            throw new LogicException('Keywords are not available');
        }

        return $this->keywords;
    }

    /**
     * @return bool
     */
    public function hasDefaultNameIdFormat()
    {
        return $this->defaultNameIdFormat !== null;
    }

    /**
     * @return NameIdFormat
     */
    public function getDefaultNameIdFormat()
    {
        if ($this->defaultNameIdFormat === null) {
            throw new LogicException('Default NameIDFormat is not known');
        }

        return $this->defaultNameIdFormat;
    }

    /**
     * @return NameIdFormatList
     */
    public function getAcceptableNameIdFormats()
    {
        return $this->acceptableNameIdFormats;
    }

    /**
     * @return bool
     */
    public function hasCertData()
    {
        return $this->certData !== null;
    }

    /**
     * @return PemEncodedX509Certificate
     */
    public function getCertData()
    {
        if ($this->certData === null) {
            throw new LogicException('Certificate data is not known');
        }

        return $this->certData;
    }

    /**
     * @return bool
     */
    public function hasGuestQualifier()
    {
        return $this->guestQualifier !== null;
    }

    /**
     * @return GuestQualifier
     */
    public function getGuestQualifier()
    {
        if ($this->guestQualifier === null) {
            throw new LogicException('Guest qualifier is not known');
        }

        return $this->guestQualifier;
    }

    /**
     * @return mixed[]
     */
    public function getFreeformProperties()
    {
        return $this->freeformProperties;
    }
}
