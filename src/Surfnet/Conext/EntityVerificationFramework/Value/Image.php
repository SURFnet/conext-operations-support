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

final class Image
{
    /** @var Url|null */
    private $url;
    /** @var mixed */
    private $width;
    /** @var mixed */
    private $height;

    /**
     * @param array  $data
     * @param string $propertyPath
     * @return Image
     */
    public static function deserialise($data, $propertyPath)
    {
        $image = new Image();

        Assert::isArray($data, 'Image data must be an array structure');

        if (array_key_exists('url', $data)) {
            $image->url = Url::fromString($data['url']);
        }

        if (array_key_exists('width', $data)) {
            Assert::string($data['width'], 'Image width must be string', sprintf('%s.width', $propertyPath));
            $image->width = $data['width'];
        }

        if (array_key_exists('height', $data)) {
            Assert::string($data['height'], 'Image height must be string', sprintf('%s.height', $propertyPath));
            $image->height = $data['height'];
        }

        return $image;
    }

    private function __construct()
    {
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return $this->url && $this->url->isValid() && $this->isWidthValid() && $this->isHeightValid();
    }

    /**
     * @return bool
     */
    private function isWidthValid()
    {
        return ((string) (int) $this->width) === $this->width && $this->width > 0;
    }

    /**
     * @return bool
     */
    private function isHeightValid()
    {
        return ((string) (int) $this->height) === $this->height && $this->height > 0;
    }

    public function __toString()
    {
        return sprintf('Image(url=%s, width=%s, height=%s)', $this->url, $this->width, $this->height);
    }
}
