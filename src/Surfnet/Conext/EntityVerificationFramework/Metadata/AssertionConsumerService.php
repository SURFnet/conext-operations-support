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

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use SimpleXMLElement;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;

final class AssertionConsumerService implements ConfiguredMetadataValidatable
{
    /** @var Binding */
    private $binding;

    /** @var Url */
    private $location;

    /** @var string|mixed */
    private $index;

    /**
     * @param array $data
     * @return AssertionConsumerService
     */
    public static function deserialize($data)
    {
        $binding = Binding::notSet();
        if (isset($data['Binding'])) {
            $binding = Binding::deserialize($data['Binding']);
        }

        $location = Url::notSet();
        if (isset($data['Location'])) {
            $location = Url::fromString($data['Location']);
        }

        $index = null;
        if (isset($data['index'])) {
            $index = $data['index'];
        }

        return new AssertionConsumerService($binding, $location, $index);
    }

    /**
     * @param SimpleXMLElement $acsXml
     * @return AssertionConsumerService
     */
    public static function fromXml(SimpleXMLElement $acsXml)
    {
        Assert::simpleXmlName($acsXml, 'AssertionConsumerService');

        $binding = Binding::notSet();
        if ($acsXml['Binding'] !== null) {
            $binding = Binding::deserialize((string) $acsXml['Binding']);
        }

        $location = Url::notSet();
        if ($acsXml['Location'] !== null) {
            $location = Url::fromString((string) $acsXml['Location']);
        }

        $index = null;
        if ($acsXml['index'] !== null) {
            $index = (string) $acsXml['index'];
        }

        return new AssertionConsumerService($binding, $location, $index);
    }

    /**
     * @param Binding      $binding
     * @param Url          $location
     * @param string|mixed $index
     */
    public function __construct(Binding $binding, Url $location, $index)
    {
        $this->binding  = $binding;
        $this->location = $location;
        $this->index    = $index;
    }

    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        $visitor->visit($this->binding, $violations, $context);
        $visitor->visit($this->location, $violations, $context);

        if (!is_string($this->index)) {
            $violations->add(
                sprintf(
                    'Binding index must set',
                    is_object($this->index) ? get_class($this->index) : gettype($this->index)
                )
            );
        } elseif (!ctype_digit($this->index)) {
            $violations->add(sprintf('Binding index must be a number, got "%s"', $this->index));
        }

        if (!$this->location->isValid() || !$this->binding->equals(Binding::create(Binding::BINDING_HTTP_POST))) {
            return;
        }

        $options  = ['headers' => ['Content-Type' => 'application/x-www-form-urlencoded'], 'allow_redirects' => false];
        try {
            $response = $context->getHttpClient()->request('POST', $this->location->getValidUrl(), $options);
        } catch (ConnectException $e) {
            $violations->add(
                sprintf('There was an error connecting to the ACS endpoint: "%s"', $e->getMessage())
            );

            return;
        } catch (RequestException $e) {
            $violations->add(
                sprintf('There was an error while communicating with ACS endpoint: "%s"', $e->getMessage())
            );

            return;
        }

        $statusCode = $response->getStatusCode();
        if ($statusCode === 404) {
            $violations->add(
                sprintf('AssertionConsumerService POST binding is not available, status code %d', $statusCode)
            );
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->binding instanceof Binding && $this->binding->isValid()
            && $this->location instanceof Url && $this->location->isValid()
            && is_string($this->index);
    }

    /**
     * @param AssertionConsumerService $other
     * @return bool
     */
    public function equals(AssertionConsumerService $other)
    {
        return $this->binding->equals($other->binding) && $this->location->equals($other->location);
    }

    /**
     * @return Url
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return mixed|string
     */
    public function getIndex()
    {
        return $this->index;
    }

    public function __toString()
    {
        return sprintf(
            'AssertionConsumerService(binding=%s, location=%s, index=%s)',
            $this->binding,
            $this->location,
            is_string($this->index) ? '"' . $this->index . '""' : '<invalid>'
        );
    }
}
