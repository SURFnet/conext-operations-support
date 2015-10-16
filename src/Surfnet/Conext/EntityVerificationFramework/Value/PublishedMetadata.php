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

final class PublishedMetadata
{
    /** @var Entity */
    private $entity;
    /** @var PemEncodedX509CertificateList */
    private $certificates;
    /** @var MultiLocaleString */
    private $entityDisplayName;
    /** @var MultiLocaleString */
    private $entityDescription;
    /** @var NameIdFormatList */
    private $nameIdFormats;
    /** @var AssertionConsumerServiceList */
    private $assertionConsumerServices;
    /** @var SingleSignOnServiceList */
    private $singleSignOnServices;
    /** @var MultiLocaleString */
    private $organisationName;
    /** @var MultiLocaleString */
    private $organisationDisplayName;
    /** @var MultiLocaleUrl */
    private $organisationUrl;
    /** @var ContactSet */
    private $contacts;

    /**
     * @param Entity                        $entity
     * @param PemEncodedX509CertificateList $certificates
     * @param MultiLocaleString             $entityDisplayName
     * @param MultiLocaleString             $entityDescription
     * @param NameIdFormatList              $nameIdFormats
     * @param AssertionConsumerServiceList  $assertionConsumerServices
     * @param SingleSignOnServiceList       $singleSignOnServices
     * @param MultiLocaleString             $organisationName
     * @param MultiLocaleString             $organisationDisplayName
     * @param MultiLocaleUrl                $organisationUrl
     * @param ContactSet                    $contacts
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Entity $entity,
        PemEncodedX509CertificateList $certificates,
        MultiLocaleString $entityDisplayName,
        MultiLocaleString $entityDescription,
        NameIdFormatList $nameIdFormats,
        AssertionConsumerServiceList $assertionConsumerServices,
        SingleSignOnServiceList $singleSignOnServices,
        MultiLocaleString $organisationName,
        MultiLocaleString $organisationDisplayName,
        MultiLocaleUrl $organisationUrl,
        ContactSet $contacts
    ) {
        $this->entity                    = $entity;
        $this->certificates              = $certificates;
        $this->entityDisplayName         = $entityDisplayName;
        $this->entityDescription         = $entityDescription;
        $this->nameIdFormats             = $nameIdFormats;
        $this->assertionConsumerServices = $assertionConsumerServices;
        $this->singleSignOnServices      = $singleSignOnServices;
        $this->organisationName          = $organisationName;
        $this->organisationDisplayName   = $organisationDisplayName;
        $this->organisationUrl           = $organisationUrl;
        $this->contacts                  = $contacts;
    }

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
