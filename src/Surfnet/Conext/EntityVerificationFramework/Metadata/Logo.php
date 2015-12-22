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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;

final class Logo implements ConfiguredMetadataValidatable
{
    /** @var LogoUrl */
    private $url;
    /** @var mixed */
    private $width;
    /** @var mixed */
    private $height;

    /**
     * @param array  $data
     * @param string $propertyPath
     * @return Logo
     */
    public static function deserialize($data, $propertyPath)
    {
        $logo = new Logo();

        Assert::isArray($data, 'Logo data must be an array structure');

        $logo->url = LogoUrl::notSet();
        if (array_key_exists('url', $data)) {
            $logo->url = LogoUrl::fromString($data['url']);
        }

        if (array_key_exists('width', $data)) {
            Assert::string($data['width'], 'Logo width must be string', sprintf('%s.width', $propertyPath));
            $logo->width = $data['width'];
        }

        if (array_key_exists('height', $data)) {
            Assert::string($data['height'], 'Logo height must be string', sprintf('%s.height', $propertyPath));
            $logo->height = $data['height'];
        }

        return $logo;
    }

    private function __construct()
    {
    }

    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        $visitor->visit($this->url, $violations, $context);

        if (!$this->isWidthValid()) {
            $violations->add(
                sprintf('Logo width "%s" is invalid: must be a number larger than 0', $this->width)
            );
        }
        if (!$this->isHeightValid()) {
            $violations->add(
                sprintf('Logo height "%s" is invalid: must be a number larger than 0', $this->height)
            );
        }
    }

    /**
     * @return bool
     */
    private function isWidthValid()
    {
        return ctype_digit($this->width) && $this->width > 0;
    }

    /**
     * @return bool
     */
    private function isHeightValid()
    {
        return ctype_digit($this->height) && $this->height > 0;
    }

    public function __toString()
    {
        return sprintf('Logo(url=%s, width=%s, height=%s)', $this->url, $this->width, $this->height);
    }
}
