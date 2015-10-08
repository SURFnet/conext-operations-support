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

final class EntityKeywords
{
    /**
     * @var string[]
     */
    private $translations;

    /**
     * @param string[] $data
     * @param string $propertyPath
     * @return EntityKeywords
     */
    public static function deserialise($data, $propertyPath)
    {
        Assert::allString($data, 'Entity keywords must contain array of translations', $propertyPath . '[]');
        Assert::allString(
            array_keys($data),
            'Entity keywords translations must be indexed by locale',
            $propertyPath . '[]'
        );

        $keywords = new EntityKeywords();
        $keywords->translations = $data;

        return $keywords;
    }

    private function __construct()
    {
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * @param EntityKeywords $other
     * @return bool
     */
    public function equals(EntityKeywords $other)
    {
        return array_diff_assoc($this->translations, $other->translations) === [];
    }

    public function __toString()
    {
        return sprintf(
            'EntityKeywords(%s)',
            join(
                ', ',
                array_map(
                    function ($locale) {
                        return sprintf('%s=%s', $locale, $this->translations[$locale]);
                    },
                    array_keys($this->translations)
                )
            )
        );
    }
}
