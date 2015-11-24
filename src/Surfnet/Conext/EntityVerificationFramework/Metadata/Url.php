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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

class Url implements ConfiguredMetadataValidatable
{
    /**
     * @var string|null
     */
    private $url;

    /**
     * @var bool|null
     */
    private $isValid;

    /**
     * @var mixed[]|null
     */
    private $parts;

    /**
     * @return static
     */
    public static function unknown()
    {
        return new static();
    }

    /**
     * @param string $string
     * @return static
     */
    public static function fromString($string)
    {
        Assert::string($string, 'URL must be string');

        $url = new static();

        if (filter_var($string, FILTER_VALIDATE_URL) === false) {
            $parts = false;
        } else {
            $parts = parse_url($string);
        }

        $isValid = $parts !== false;

        $url->url   = $string;
        $url->isValid = $isValid;
        $url->parts = ($parts ?: []) + [
            'scheme'   => null,
            'host'     => null,
            'port'     => null,
            'user'     => null,
            'pass'     => null,
            'path'     => null,
            'query'    => null,
            'fragment' => null,
        ];

        return $url;
    }

    final private function __construct()
    {
    }

    public function validate(ConfiguredMetadataValidator $validator, ConfiguredMetadataValidationContext $context)
    {
        if (!$this->isValid()) {
            $validator->addViolation(sprintf('URL "%s" is not valid', $this->url));
        }
    }

    /**
     * @param string $scheme
     * @return bool
     */
    public function isScheme($scheme)
    {
        if (!$this->isValid()) {
            throw new LogicException('Cannot check whether URL has a certain scheme; the URL is not valid');
        }

        Assert::string($scheme);

        return strtolower($this->parts['scheme']) === strtolower($scheme);
    }

    /**
     * @param string $pattern Regular expression pattern suited for `preg_match()`.
     * @return bool
     */
    public function matches($pattern)
    {
        if (!$this->isValid()) {
            throw new LogicException('Cannot match URL; it is not valid');
        }

        return preg_match($pattern, $this->url) === 1;
    }

    /**
     * @return string
     */
    public function getValidUrl()
    {
        if (!$this->isValid()) {
            throw new LogicException('Cannot retrieve valid URL; it is not valid');
        }

        return $this->url;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid === true;
    }

    /**
     * @param Url $other
     * @return bool
     */
    public function equals(Url $other)
    {
        return $this == $other;
    }

    public function __toString()
    {
        return $this->url;
    }
}
