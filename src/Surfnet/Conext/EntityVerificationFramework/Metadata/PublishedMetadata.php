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

use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\EntityVerificationFramework\Value\EntityId;

class PublishedMetadata
{
    /** @var EntityId */
    private $entityId;
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
    /** @var Organisation */
    private $organisation;
    /** @var ContactSet */
    private $contacts;

    /**
     * @param EntityId                      $entityId
     * @param PemEncodedX509CertificateList $certificates
     * @param MultiLocaleString             $entityDisplayName
     * @param MultiLocaleString             $entityDescription
     * @param NameIdFormatList              $nameIdFormats
     * @param AssertionConsumerServiceList  $assertionConsumerServices
     * @param SingleSignOnServiceList       $singleSignOnServices
     * @param Organisation                  $organisation
     * @param ContactSet                    $contacts
     */
    public function __construct(
        EntityId $entityId,
        PemEncodedX509CertificateList $certificates,
        MultiLocaleString $entityDisplayName,
        MultiLocaleString $entityDescription,
        NameIdFormatList $nameIdFormats,
        AssertionConsumerServiceList $assertionConsumerServices,
        SingleSignOnServiceList $singleSignOnServices,
        Organisation $organisation,
        ContactSet $contacts
    ) {
        $this->entityId                  = $entityId;
        $this->certificates              = $certificates;
        $this->entityDisplayName         = $entityDisplayName;
        $this->entityDescription         = $entityDescription;
        $this->nameIdFormats             = $nameIdFormats;
        $this->assertionConsumerServices = $assertionConsumerServices;
        $this->singleSignOnServices      = $singleSignOnServices;
        $this->organisation              = $organisation;
        $this->contacts                  = $contacts;
    }

    /**
     * @param Entity $entity
     * @return bool
     */
    public function isPublishedFor(Entity $entity)
    {
        $hasAcss = count($this->assertionConsumerServices) > 0;
        $hasSsos = count($this->singleSignOnServices) > 0;

        return $entity->hasEntityId($this->entityId)
            && ($entity->isServiceProvider() && $hasAcss || $entity->isIdentityProvider() && $hasSsos);
    }
}
