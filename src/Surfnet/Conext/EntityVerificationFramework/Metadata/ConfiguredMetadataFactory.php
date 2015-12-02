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
use Surfnet\Conext\EntityVerificationFramework\Value\EntityType;

/**
 * @SuppressWarnings(PHPMD)
 */
final class ConfiguredMetadataFactory
{
    /**
     * @param mixed $data
     * @return ConfiguredMetadata
     */
    public static function deserialise($data)
    {
        Assert::isArray($data, 'Configured metadata data must be an array structure');

        $entityType = self::getEntityType($data);

        $publishedMetadataUrl = null;
        if (array_key_exists('metadataUrl', $data)) {
            Assert::string($data['metadataUrl'], 'Published metadata URL is not a string', 'metadataUrl');
            $publishedMetadataUrl = Url::fromString($data['metadataUrl']);
        }

        Assert::keyExists($data, 'metadata', 'Doesn\'t contain "metadata" key');
        Assert::isArray($data['metadata'], '"metadata" key must contain an array structure');
        $metadataData = $data['metadata'];

        if (isset($metadataData['contacts'])) {
            $contacts = ContactSet::deserialise($metadataData['contacts'], 'metadata.contacts');
        } else {
            $contacts = new ContactSet();
        }

        $name = new Name();
        if (isset($metadataData['name'])) {
            $name = new Name($metadataData['name']);
        }

        $description = new Description();
        if (isset($metadataData['description'])) {
            $description = new Description($metadataData['description']);
        }

        if (isset($metadataData['logo'])) {
            $logoData = $metadataData['logo'];
            Assert::isArray($logoData, 'SP metadata\'s "logo" key must contain an array', 'metadata.logo');

            $logos = LogoList::deserialise($metadataData['logo'], 'metadata.logo');
        } else {
            $logos = new LogoList();
        }

        $signRedirects = null;
        if (isset($metadataData['redirect']['sign'])) {
            Assert::boolean($metadataData['redirect']['sign'], 'Redirect sign flag must be boolean');
            $signRedirects = $metadataData['redirect']['sign'];
        }

        $defaultNameIdFormat = NameIdFormat::unknown();
        if (isset($metadataData['NameIDFormat'])) {
            $defaultNameIdFormat = NameIdFormat::fromUrn($metadataData['NameIDFormat']);
        }

        $acceptableNameIdFormats = new NameIdFormatList();
        if (isset($metadataData['NameIDFormats'])) {
            Assert::isArray(
                $metadataData['NameIDFormats'],
                'Metadata "NameIDFormats" must be an array',
                'metadata.NameIDFormats'
            );
            $acceptableNameIdFormats = new NameIdFormatList(
                array_map(
                    function ($data) {
                        return NameIdFormat::fromUrn($data);
                    },
                    $metadataData['NameIDFormats']
                )
            );
        }

        if (isset($metadataData['AssertionConsumerService'])) {
            $assertionConsumerServices = AssertionConsumerServiceList::deserialise(
                $metadataData['AssertionConsumerService'],
                'metadata.AssertionConsumerService'
            );
        } else {
            $assertionConsumerServices = new AssertionConsumerServiceList();
        }

        $url = new MultiLocaleUrl();
        if (isset($metadataData['url'])) {
            $url = MultiLocaleUrl::deserialise($metadataData['url'], 'metadata.url');
        }

        if (isset($metadataData['SingleSignOnService'])) {
            $singleSignOnServices = SingleSignOnServiceList::deserialise(
                $metadataData['SingleSignOnService'],
                'metadata.SingleSignOnService'
            );
        } else {
            $singleSignOnServices = new SingleSignOnServiceList();
        }

        $keywords = new Keywords();
        if (isset($metadataData['keywords'])) {
            $keywords = new Keywords($metadataData['keywords']);
        }

        $certData = null;
        if (isset($metadataData['certData'])) {
            $certData = new PemEncodedX509Certificate($metadataData['certData']);
        }

        $coinData = isset($metadataData['coin']) ? $metadataData['coin'] : [];
        $guestQualifier = null;
        if (isset($coinData['guest_qualifier'])) {
            $guestQualifier = new GuestQualifier($coinData['guest_qualifier']);
        }

        $freeformProperties = [];
        $multiLocaleFreeformProperties = [
            'OrganizationDisplayName', 'OrganizationName', 'OrganizationURL', 'displayName', 'keywords'
        ];
        foreach ($multiLocaleFreeformProperties as $property) {
            if (!isset($metadataData[$property])) {
                continue;
            }

            Assert::isArray(
                $metadataData[$property],
                sprintf('Multi-locale metadata property "%s" must contain an array', $property),
                sprintf('metadata.%s', $property)
            );

            foreach ($metadataData[$property] as $locale => $value) {
                $freeformProperties[sprintf('%s:%s', $property, $locale)] = $value;
            }
        }

        if (isset($coinData['publish_in_edugain'])) {
            Assert::boolean($coinData['publish_in_edugain'], null, 'metadata.coin.publish_in_edugain');
            $freeformProperties['coin:publish_in_edugain'] = $coinData['publish_in_edugain'];
        }

        if (isset($coinData['publish_in_edugain_date'])) {
            Assert::string($coinData['publish_in_edugain_date'], null, 'metadata.coin.publish_in_edugain_date');
            $freeformProperties['coin:publish_in_edugain_date'] = $coinData['publish_in_edugain_date'];
        }

        if (isset($coinData['schachomeorganization'])) {
            Assert::string($coinData['schachomeorganization'], null, 'metadata.coin.schachomeorganization');
            $freeformProperties['coin:schachomeorganization'] = $coinData['schachomeorganization'];
        }

        $scopeData = isset($metadataData['shibmd']['scope']) ? $metadataData['shibmd']['scope'] : [];
        $scopes = ShibbolethMetadataScopeList::deserialise($scopeData, 'metadata.shibmd.scope');

        return new ConfiguredMetadata(
            $entityType,
            $assertionConsumerServices,
            $singleSignOnServices,
            $defaultNameIdFormat,
            $acceptableNameIdFormats,
            $contacts,
            $keywords,
            $logos,
            $name,
            $description,
            $url,
            $scopes,
            $publishedMetadataUrl,
            $certData,
            $signRedirects,
            $guestQualifier,
            $freeformProperties
        );
    }

    /**
     * @param array  $data
     * @return EntityType
     */
    private static function getEntityType(array $data)
    {
        Assert::keyExists($data, 'type', "Configured metadata doesn't have a type");
        Assert::string($data['type'], "Configured metadata's type is not a string", 'type');

        if ($data['type'] === 'saml20-sp') {
            $entityType = EntityType::SP();
        } elseif ($data['type'] === 'saml20-idp') {
            $entityType = EntityType::IdP();
        } else {
            throw new LogicException(
                sprintf('Illegal entity type "%s" encountered in configured metadata', $data['type'])
            );
        }

        return $entityType;
    }
}
