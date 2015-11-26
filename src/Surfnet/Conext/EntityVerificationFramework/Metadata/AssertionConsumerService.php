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

use SimpleXMLElement;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;

final class AssertionConsumerService
{
    /** @var Binding|null */
    private $binding;

    /** @var Url|null */
    private $location;

    /**
     * @param array $data
     * @return AssertionConsumerService
     */
    public static function deserialise($data)
    {
        $binding = null;
        if (isset($data['Binding'])) {
            $binding = Binding::deserialise($data['Binding']);
        }

        $location = null;
        if (isset($data['Location'])) {
            $location = Url::fromString($data['Location']);
        }

        return new AssertionConsumerService($binding, $location);
    }

    public static function fromXml(SimpleXMLElement $acsXml)
    {
        Assert::simpleXmlName($acsXml, 'AssertionConsumerService');

        $binding = null;
        if ($acsXml['Binding'] !== null) {
            $binding = Binding::deserialise((string) $acsXml['Binding']);
        }

        $location = null;
        if ($acsXml['Location'] !== null) {
            $location = Url::fromString((string) $acsXml['Location']);
        }

        return new AssertionConsumerService($binding, $location);
    }

    /**
     * @param Binding|null $binding
     * @param Url|null     $location
     */
    private function __construct(Binding $binding = null, Url $location = null)
    {
        $this->binding = $binding;
        $this->location = $location;
    }

    /**
     * @return bool
     */
    public function hasBinding()
    {
        return $this->binding !== null;
    }

    /**
     * @return Binding
     */
    public function getBinding()
    {
        if ($this->binding === null) {
            throw new LogicException('AssertionConsumerService Binding is not known');
        }

        return $this->binding;
    }

    /**
     * @return bool
     */
    public function hasLocation()
    {
        return $this->location !== null;
    }

    /**
     * @return Url
     */
    public function getLocation()
    {
        if ($this->location === null) {
            throw new LogicException('AssertionConsumerService Location is not known');
        }

        return $this->location;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isBindingValid() && $this->isLocationValid();
    }

    /**
     * @return bool
     */
    public function isBindingValid()
    {
        return $this->binding && $this->binding->isValid();
    }

    /**
     * @return bool
     */
    public function isLocationValid()
    {
        return $this->location && $this->location->isValid();
    }

    /**
     * @param AssertionConsumerService $other
     * @return bool
     */
    public function equals(AssertionConsumerService $other)
    {
        if ($this->binding === null || $other->binding === null) {
            $valid = $this->binding === $other->binding;
        } else {
            $valid = $this->binding->equals($other->binding);
        }

        if ($this->location === null || $other->location === null) {
            return $valid && $this->location === $other->location;
        } else {
            return $valid && $this->location->equals($other->location);
        }
    }

    public function __toString()
    {
        return sprintf('AssertionConsumerService(Binding=%s, Location=%s)', $this->binding, $this->location);
    }
}