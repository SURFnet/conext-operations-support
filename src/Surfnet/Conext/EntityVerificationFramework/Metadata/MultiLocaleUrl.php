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

class MultiLocaleUrl
{
    /**
     * @var Url[]
     */
    private $urls;

    /**
     * @param string[] $data URLs indexed by locale string
     * @param string   $propertyPath
     * @return static
     */
    public static function deserialize($data, $propertyPath)
    {
        Assert::allString($data, 'All URLs must be strings', $propertyPath);

        return new static(
            array_map('Surfnet\Conext\EntityVerificationFramework\Metadata\Url::fromString', $data)
        );
    }

    /**
     * @param Url[] $urls URLs indexed by locale string
     */
    final public function __construct(array $urls = [])
    {
        Assert::allIsInstanceOf($urls, Url::class, 'URLs must be instances of Url');
        Assert::allString(array_keys($urls), 'URLs must be indexed by locale');

        $this->urls = $urls;
    }

    /**
     * @param string $locale
     * @param Url $url
     * @return MultiLocaleUrl
     */
    public function add($locale, Url $url)
    {
        Assert::string($locale, 'Locale must be string');
        Assert::notBlank($locale, 'Locale may not be blank');

        $applicationUrl = clone $this;
        $applicationUrl->urls[$locale] = $url;

        return $applicationUrl;
    }

    /**
     * @param string $locale
     * @return bool
     */
    public function hasValidUrlForLocale($locale)
    {
        Assert::string($locale, 'Locale must be string', 'locale');

        return array_key_exists($locale, $this->urls) && $this->urls[$locale]->isValid();
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->urls as $url) {
            if (!$url->isValid()) {
                return false;
            }
        }

        return true;
    }

    public function equals(MultiLocaleUrl $other)
    {
        $thisUrls = $this->urls;
        $otherUrls = $other->urls;
        ksort($thisUrls);
        ksort($otherUrls);

        if (array_keys($thisUrls) !== array_keys($otherUrls)) {
            return false;
        }

        foreach ($thisUrls as $locale => $thisUrl) {
            if (!$thisUrl->equals($otherUrls[$locale])) {
                return false;
            }
        }

        return true;
    }

    public function __toString()
    {
        return sprintf(
            'MultiLocaleUrl(%s)',
            join(
                ', ',
                array_map(
                    function ($locale) {
                        return sprintf('%s=%s', $locale, $this->urls[$locale]);
                    },
                    array_keys($this->urls)
                )
            )
        );
    }

    /**
     * @return Url[] URLs indexed by their locales
     */
    protected function getUrls()
    {
        return $this->urls;
    }
}
