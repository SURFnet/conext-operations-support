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
 * Class is not final, because it is mocked.
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateFields)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class ConfiguredMetadata
{
    /** @var EntityType */
    private $entityType;
    /** @var string|null */
    private $publishedMetadataUrl;
    /** @var AssertionConsumerService[] */
    private $assertionConsumerServices = [];
    /** @var SingleSignOnService[] */
    private $singleSignOnServices = [];
    /** @var ContactSet */
    private $contacts;
    /** @var EntityName */
    private $name;
    /** @var Image[] */
    private $logos = [];
    /** @var boolean|null */
    private $signRedirects;
    /** @var ApplicationUrl|null */
    private $url;
    /** @var EntityKeywords|null */
    private $keywords;
    /** @var NameIdFormat|null */
    private $defaultNameIdFormat;
    /** @var NameIdFormat[] */
    private $acceptableNameIdFormats = [];
    /** @var PemEncodedX509Certificate|null */
    private $certData;
    /** @var boolean|null */
    private $guestQualifier;
    /** @var mixed[] Array indexed by string keys */
    private $freeformProperties = [];

    /**
     * @param mixed $data
     * @return ConfiguredMetadata
     */
    public static function deserialise($data)
    {
        Assert::isArray($data, 'Configure metadata data must be an array structure');

        $metadata = new self();
        $metadata->entityType = self::getEntityType($data);

        if (array_key_exists('metadataUrl', $data)) {
            Assert::string($data['metadataUrl'], 'Published metadata URL is not a string', 'metadataUrl');
            $metadata->publishedMetadataUrl = $data['metadataUrl'];
        }

        Assert::keyExists($data, 'metadata', 'Doesn\'t contain "metadata" key');
        Assert::isArray($data['metadata'], '"metadata" key must contain an array structure');
        $metadataData = $data['metadata'];

        Assert::keyExists('contacts', $metadataData, "Metadata doesn't contain contacts", 'metadata');
        if (isset($metadata['contacts'])) {
            $metadata->contacts = ContactSet::deserialise($metadataData['contacts'], 'metadata.contacts');
        }

        if (isset($metadataData['name'])) {
            $metadata->name = EntityName::deserialise($metadataData['name'], 'metadata.name');
        }

        if (isset($metadataData['logo'])) {
            $logoData = $metadataData['logo'];
            Assert::isArray($logoData, 'SP metadata\'s "logo" key must contain an array', 'metadata.logo');

            $metadata->logos = array_map(
                function ($data) {
                    return Image::deserialise($data, 'metadata.logo[]');
                },
                $logoData
            );
        }

        if (isset($metadataData['redirect']['sign'])) {
            Assert::boolean($metadataData['redirect']['sign'], 'Redirect sign flag must be boolean');
            $metadata->signRedirects = $metadataData['redirect']['sign'];
        }

        if (isset($metadataData['NameIDFormat'])) {
            $metadata->defaultNameIdFormat =
                NameIdFormat::deserialise($metadataData['NameIDFormat'], 'metadata.NameIDFormat');
        }

        if (isset($metadataData['NameIDFormats'])) {
            Assert::isArray(
                $metadataData['NameIDFormats'],
                'Metadata "NameIDFormats" must be an array',
                'metadata.NameIDFormats'
            );
            $metadata->acceptableNameIdFormats = array_map(
                function ($data) {
                    return NameIdFormat::deserialise($data, 'metadata.NameIDFormats[]');
                },
                $metadataData['NameIDFormats']
            );
        }

        if (isset($metadataData['AssertionConsumerService'])) {
            $assertionConsumerServiceData = $metadataData['AssertionConsumerService'];
            Assert::isArray(
                $assertionConsumerServiceData,
                'SP metadata\'s "AssertionConsumerService" key must contain an array',
                'metadata.AssertionConsumerService'
            );

            $metadata->assertionConsumerServices = array_map(
                function ($data) {
                    return AssertionConsumerService::deserialise($data);
                },
                $assertionConsumerServiceData
            );
        }

        if (isset($metadataData['url'])) {
            $metadata->url = ApplicationUrl::deserialise($metadataData['url'], 'metadata.url');
        }

        if (isset($metadataData['SingleSignOnService'])) {
            $singleSignOnServiceData = $metadataData['SingleSignOnService'];
            Assert::isArray(
                $singleSignOnServiceData,
                'SP metadata\'s "SingleSignOnService" key must contain an array',
                'metadata.SingleSignOnService'
            );

            $metadata->singleSignOnServices = array_map(
                function ($data) {
                    return SingleSignOnService::deserialise($data);
                },
                $singleSignOnServiceData
            );
        }

        if (isset($metadataData['keywords'])) {
            $metadata->keywords = EntityKeywords::deserialise($metadataData['keywords'], 'metadata.keywords');
        }

        if (isset($metadataData['certData'])) {
            $metadata->certData = PemEncodedX509Certificate::deserialise(
                $metadataData['certData'],
                'metadata.certData'
            );
        }

        $coinData = isset($metadataData['coin']) ? $metadataData['coin'] : [];
        if (isset($coinData['guest_qualifier'])) {
            Assert::boolean($coinData['guest_qualifier'], null, 'metadata.coin.guest_qualifier');
            $metadata->guestQualifier = $coinData['guest_qualifier'];
        }

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
                $metadata->freeformProperties[sprintf('%s:%s', $property, $locale)] = $value;
            }
        }

        if (isset($coinData['publish_in_edugain'])) {
            Assert::boolean($coinData['publish_in_edugain'], null, 'metadata.coin.publish_in_edugain');
            $metadata->freeformProperties['coin:publish_in_edugain'] = $coinData['publish_in_edugain'];
        }

        if (isset($coinData['publish_in_edugain_date'])) {
            Assert::string($coinData['publish_in_edugain_date'], null, 'metadata.coin.publish_in_edugain_date');
            $metadata->freeformProperties['coin:publish_in_edugain_date'] = $coinData['publish_in_edugain_date'];
        }

        if (isset($coinData['schachomeorganization'])) {
            Assert::string($coinData['schachomeorganization'], null, 'metadata.coin.schachomeorganization');
            $metadata->freeformProperties['coin:schachomeorganization'] = $coinData['schachomeorganization'];
        }

        $scopeData = isset($metadataData['shibmd']['scope']) ? $metadataData['shibmd']['scope'] : [];
        for ($i = 0; $i <= 5; ++$i) {
            if (isset($scopeData[$i]['allowed'])) {
                Assert::string($scopeData[$i]['allowed'], null, sprintf('metadata.shibmd.scope[%d].allowed', $i));
                $metadata->freeformProperties[sprintf('shibmd:scope:%d:allowed', $i)] = $scopeData[$i]['allowed'];
            }

            if (isset($scopeData[$i]['regexp'])) {
                Assert::string($scopeData[$i]['regexp'], null, sprintf('metadata.shibmd.scope[%d].regexp', $i));
                $metadata->freeformProperties[sprintf('shibmd:scope:%d:regexp', $i)] = $scopeData[$i]['regexp'];
            }
        }

        return $metadata;
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

    private function __construct()
    {
        $this->contacts = new ContactSet();
        $this->name = new EntityName();
    }
}
