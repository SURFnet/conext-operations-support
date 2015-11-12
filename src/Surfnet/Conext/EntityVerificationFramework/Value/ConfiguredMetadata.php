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

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
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
        if ($publishedMetadataUrl !== null) {
            Assert::string($publishedMetadataUrl, null, 'publishedMetadataUrl');
        }

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
     * @return null|Url
     */
    public function getPublishedMetadataUrl()
    {
        return $this->publishedMetadataUrl;
    }
}
