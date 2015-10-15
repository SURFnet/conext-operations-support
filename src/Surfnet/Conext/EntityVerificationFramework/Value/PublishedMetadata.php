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

use SimpleXMLElement;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException;
use Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException;

/**
 * @SuppressWarnings(PHPMD)
 */
final class PublishedMetadata
{
    const RECURSIVE = true;

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
     * @param SimpleXMLElement $entityDescriptorXml
     * @return PublishedMetadata
     * @throws AssertionFailedException
     * @throws InvalidArgumentException
     */
    public static function fromEntityDescriptorXml(SimpleXMLElement $entityDescriptorXml)
    {
        Assert::simpleXmlName($entityDescriptorXml, 'EntityDescriptor');

        $entityDescriptorXml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $entityDescriptorXml->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $entityDescriptorXml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        $publishedMetadata = new PublishedMetadata();

        $entityId = new EntityId((string) $entityDescriptorXml['entityID']);
        $spSsoDescriptorCount = count($entityDescriptorXml->xpath('md:SPSSODescriptor'));
        $idpSsoDescriptorCount = count($entityDescriptorXml->xpath('md:IDPSSODescriptor'));

        if ($spSsoDescriptorCount + $idpSsoDescriptorCount !== 1) {
            throw new InvalidArgumentException(
                sprintf(
                    'Can only handle one of the %d SP and %s IDP SSO descriptors',
                    $spSsoDescriptorCount,
                    $idpSsoDescriptorCount
                )
            );
        } elseif ($spSsoDescriptorCount === 1) {
            $publishedMetadata->entity = new Entity($entityId, EntityType::SP());
        } else {
            $publishedMetadata->entity = new Entity($entityId, EntityType::IdP());
        }

        $certificateXmls = $entityDescriptorXml->xpath('ds:Signature/ds:KeyInfo/ds:X509Data/ds:X509Certificate');
        foreach ($certificateXmls as $x509CertificateXml) {
            $certificate = new PemEncodedX509Certificate((string) $x509CertificateXml);
            $publishedMetadata->certificates = $publishedMetadata->certificates->add($certificate);
        }

        $displayNameXmls = $entityDescriptorXml->xpath(
            '(md:SPSSODescriptor|md:IDPSSODescriptor)/md:Extensions/mdui:UIInfo/mdui:DisplayName'
        );
        foreach ($displayNameXmls as $displayNameXml) {
            $locale      = (string) $displayNameXml->xpath('@xml:lang')[0];
            $translation = (string) $displayNameXml;
            $publishedMetadata->entityDisplayName =
                $publishedMetadata->entityDisplayName->add($locale, $translation);
        }

        $descriptionXmls = $entityDescriptorXml->xpath(
            '(md:SPSSODescriptor|md:IDPSSODescriptor)/md:Extensions/mdui:UIInfo/mdui:Description'
        );
        foreach ($descriptionXmls as $descriptionXml) {
            $locale      = (string) $descriptionXml->xpath('@xml:lang')[0];
            $translation = (string) $descriptionXml;
            $publishedMetadata->entityDescription =
                $publishedMetadata->entityDescription->add($locale, $translation);
        }

        $nameIdFormatXmls = $entityDescriptorXml->xpath('(md:SPSSODescriptor|md:IDPSSODescriptor)/md:NameIDFormat');
        foreach ($nameIdFormatXmls as $nameIdFormatXml) {
            $nameIdFormat = new NameIdFormat((string) $nameIdFormatXml);
            $publishedMetadata->nameIdFormats = $publishedMetadata->nameIdFormats->add($nameIdFormat);
        }

        $ssoXmls = $entityDescriptorXml->xpath('md:IDPSSODescriptor/md:SingleSignOnService');
        foreach ($ssoXmls as $ssoXml) {
            $sso = SingleSignOnService::fromXml($ssoXml);
            $publishedMetadata->singleSignOnServices =
                $publishedMetadata->singleSignOnServices->add($sso);
        }

        $acsXmls = $entityDescriptorXml->xpath('md:SPSSODescriptor/md:AssertionConsumerService');
        foreach ($acsXmls as $acsXml) {
            $acs = AssertionConsumerService::fromXml($acsXml);
            $publishedMetadata->assertionConsumerServices =
                $publishedMetadata->assertionConsumerServices->add($acs);
        }

        $organisationNameXmls = $entityDescriptorXml->xpath('md:Organization/md:OrganizationName');
        foreach ($organisationNameXmls as $organisationNameXml) {
            $locale      = (string) $organisationNameXml->xpath('@xml:lang')[0];
            $translation = (string) $organisationNameXml;
            $publishedMetadata->organisationName =
                $publishedMetadata->organisationName->add($locale, $translation);
        }

        $organisationDisplayNameXmls = $entityDescriptorXml->xpath('md:Organization/md:OrganizationDisplayName');
        foreach ($organisationDisplayNameXmls as $organisationDisplayNameXml) {
            $locale      = (string) $organisationDisplayNameXml->xpath('@xml:lang')[0];
            $translation = (string) $organisationDisplayNameXml;
            $publishedMetadata->organisationName =
                $publishedMetadata->organisationName->add($locale, $translation);
        }

        $organisationUrlXmls = $entityDescriptorXml->xpath('md:Organization/md:OrganizationUrl');
        foreach ($organisationUrlXmls as $organisationUrlXml) {
            $locale      = (string) $organisationUrlXml->xpath('@xml:lang')[0];
            $translation = (string) $organisationUrlXml;
            $publishedMetadata->organisationName =
                $publishedMetadata->organisationName->add($locale, $translation);
        }

        foreach ($entityDescriptorXml->xpath('md:ContactPerson') as $contactPersonXml) {
            $contactType = null;
            $emailAddress = null;
            $givenName = null;
            $surName = null;

            if ($contactPersonXml['contactType']) {
                $contactType = new ContactType((string) $contactPersonXml['contactType']);
            }

            if (count($contactPersonXml->EmailAddress) > 0) {
                $emailAddress = new EmailAddress((string) $contactPersonXml->EmailAddress);
            }

            if (count($contactPersonXml->GivenName) > 0) {
                $givenName = (string) $contactPersonXml->GivenName;
            }

            if (count($contactPersonXml->SurName) > 0) {
                $givenName = (string) $contactPersonXml->SurName;
            }

            $contact = new Contact($contactType, $emailAddress, $givenName, $surName);
            $publishedMetadata->contacts = $publishedMetadata->contacts->add($contact);
        }

        return $publishedMetadata;
    }

    private function __construct()
    {
        $this->certificates              = new PemEncodedX509CertificateList();
        $this->entityDisplayName         = new MultiLocaleString();
        $this->entityDescription         = new MultiLocaleString();
        $this->nameIdFormats             = new NameIdFormatList();
        $this->singleSignOnServices      = new SingleSignOnServiceList();
        $this->assertionConsumerServices = new AssertionConsumerServiceList();
        $this->organisationName          = new MultiLocaleString();
        $this->organisationDisplayName   = new MultiLocaleString();
        $this->organisationUrl           = new MultiLocaleUrl();
        $this->contacts                  = new ContactSet();
    }

    /**
     * @return Entity
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
