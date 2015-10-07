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

final class ApplicationUrl
{
    /**
     * @var Url[]
     */
    private $urls;

    public static function deserialise($data, $propertyPath)
    {
        Assert::allString($data, 'Application URLs must be strings', $propertyPath . '[]');
        Assert::allString(
            array_keys($data),
            'Application URLs must be indexed by locale',
            $propertyPath . '[]'
        );

        $applicationUrl = new ApplicationUrl();
        $applicationUrl->urls = array_map('Surfnet\Conext\EntityVerificationFramework\Value\Url::deserialise', $data);

        return $applicationUrl;
    }

    private function __construct()
    {
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

    public function equals(ApplicationUrl $other)
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
            'ApplicationUrl(%s)',
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
}
