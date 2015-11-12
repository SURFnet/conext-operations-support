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
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class PublishedMetadataFactory
{
    /**
     * @param SimpleXMLElement $xml
     * @return PublishedMetadataList
     * @throws AssertionFailedException
     * @throws InvalidArgumentException
     */
    public static function fromMetadataXml(SimpleXMLElement $xml)
    {
        $xml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');

        $metadatas = new PublishedMetadataList();

        $entityDescriptorXmls = $xml->xpath('/md:EntitiesDescriptor/md:EntityDescriptor | /md:EntityDescriptor');
        foreach ($entityDescriptorXmls as $entityDescriptorXml) {
            $metadatas = $metadatas->add(self::fromEntityDescriptorXml($entityDescriptorXml));
        }

        return $metadatas;
    }

    /**
     * @param SimpleXMLElement $entityDescriptorXml
     * @return PublishedMetadata
     * @throws AssertionFailedException
     * @throws InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD)
     */
    private static function fromEntityDescriptorXml(SimpleXMLElement $entityDescriptorXml)
    {
        Assert::simpleXmlName($entityDescriptorXml, 'EntityDescriptor');

        $entityDescriptorXml->registerXPathNamespace('md', 'urn:oasis:names:tc:SAML:2.0:metadata');
        $entityDescriptorXml->registerXPathNamespace('ds', 'http://www.w3.org/2000/09/xmldsig#');
        $entityDescriptorXml->registerXPathNamespace('mdui', 'urn:oasis:names:tc:SAML:metadata:ui');

        $entityId = new EntityId((string) $entityDescriptorXml['entityID']);

        $certificateXmls = $entityDescriptorXml->xpath('ds:Signature/ds:KeyInfo/ds:X509Data/ds:X509Certificate');
        $certificates = new PemEncodedX509CertificateList(
            array_map(
                function (SimpleXMLElement $x509CertificateXml) {
                    return new PemEncodedX509Certificate((string) $x509CertificateXml);
                },
                $certificateXmls
            )
        );

        $displayNameXmls = $entityDescriptorXml->xpath(
            '(md:SPSSODescriptor|md:IDPSSODescriptor)/md:Extensions/mdui:UIInfo/mdui:DisplayName'
        );
        $entityDisplayName = new MultiLocaleString();
        foreach ($displayNameXmls as $displayNameXml) {
            $locale      = (string) $displayNameXml->xpath('@xml:lang')[0];
            $translation = (string) $displayNameXml;
            $entityDisplayName = $entityDisplayName->add($locale, $translation);
        }

        $descriptionXmls = $entityDescriptorXml->xpath(
            '(md:SPSSODescriptor|md:IDPSSODescriptor)/md:Extensions/mdui:UIInfo/mdui:Description'
        );
        $entityDescription = new MultiLocaleString();
        foreach ($descriptionXmls as $descriptionXml) {
            $locale      = (string) $descriptionXml->xpath('@xml:lang')[0];
            $translation = (string) $descriptionXml;
            $entityDescription = $entityDescription->add($locale, $translation);
        }

        $nameIdFormatXmls = $entityDescriptorXml->xpath('(md:SPSSODescriptor|md:IDPSSODescriptor)/md:NameIDFormat');
        $nameIdFormats = new NameIdFormatList();
        foreach ($nameIdFormatXmls as $nameIdFormatXml) {
            $nameIdFormat = new NameIdFormat((string) $nameIdFormatXml);
            $nameIdFormats = $nameIdFormats->add($nameIdFormat);
        }

        $ssoXmls = $entityDescriptorXml->xpath('md:IDPSSODescriptor/md:SingleSignOnService');
        $singleSignOnServices = new SingleSignOnServiceList();
        foreach ($ssoXmls as $ssoXml) {
            $sso = SingleSignOnService::fromXml($ssoXml);
            $singleSignOnServices = $singleSignOnServices->add($sso);
        }

        $acsXmls = $entityDescriptorXml->xpath('md:SPSSODescriptor/md:AssertionConsumerService');
        $assertionConsumerServices = new AssertionConsumerServiceList();
        foreach ($acsXmls as $acsXml) {
            $acs = AssertionConsumerService::fromXml($acsXml);
            $assertionConsumerServices = $assertionConsumerServices->add($acs);
        }

        $organisationNameXmls = $entityDescriptorXml->xpath('md:Organization/md:OrganizationName');
        $organisationName = new MultiLocaleString();
        foreach ($organisationNameXmls as $organisationNameXml) {
            $locale      = (string) $organisationNameXml->xpath('@xml:lang')[0];
            $translation = (string) $organisationNameXml;
            $organisationName = $organisationName->add($locale, $translation);
        }

        $organisationDisplayNameXmls = $entityDescriptorXml->xpath('md:Organization/md:OrganizationDisplayName');
        $organisationDisplayName = new MultiLocaleString();
        foreach ($organisationDisplayNameXmls as $organisationDisplayNameXml) {
            $locale      = (string) $organisationDisplayNameXml->xpath('@xml:lang')[0];
            $translation = (string) $organisationDisplayNameXml;
            $organisationName = $organisationName->add($locale, $translation);
        }

        $organisationUrlXmls = $entityDescriptorXml->xpath('md:Organization/md:OrganizationUrl');
        $organisationUrl = new MultiLocaleUrl();
        foreach ($organisationUrlXmls as $organisationUrlXml) {
            $locale = (string) $organisationUrlXml->xpath('@xml:lang')[0];
            $url    = (string) $organisationUrlXml;
            $organisationUrl = $organisationUrl->add($locale, Url::fromString($url));
        }

        $contacts = new ContactSet();
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
            $contacts = $contacts->add($contact);
        }

        return new PublishedMetadata(
            $entityId,
            $certificates,
            $entityDisplayName,
            $entityDescription,
            $nameIdFormats,
            $assertionConsumerServices,
            $singleSignOnServices,
            new Organisation($organisationName, $organisationDisplayName, $organisationUrl),
            $contacts
        );
    }
}
