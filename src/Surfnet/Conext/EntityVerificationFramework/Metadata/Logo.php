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

final class Logo
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
     * @return Logo
     */
    public static function deserialise($data, $propertyPath)
    {
        $logo = new Logo();

        Assert::isArray($data, 'Logo data must be an array structure');

        if (array_key_exists('url', $data)) {
            $logo->url = Url::fromString($data['url']);
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
    public function isWidthValid()
    {
        return ((string) (int) $this->width) === $this->width && $this->width > 0;
    }

    /**
     * @return bool
     */
    public function isHeightValid()
    {
        return ((string) (int) $this->height) === $this->height && $this->height > 0;
    }

    /**
     * @return bool
     */
    public function hasUrl()
    {
        return $this->url !== null;
    }

    /**
     * @return Url
     */
    public function getUrl()
    {
        if ($this->url === null) {
            throw new LogicException('Logo has no URL');
        }

        return $this->url;
    }

    public function __toString()
    {
        return sprintf('Logo(url=%s, width=%s, height=%s)', $this->url, $this->width, $this->height);
    }
}
