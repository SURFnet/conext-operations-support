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

final class LogoList
{
    /**
     * @var Logo[]
     */
    private $logos;

    /**
     * @param mixed  $data
     * @param string $propertyPath
     * @return LogoList
     */
    public static function deserialise($data, $propertyPath)
    {
        $list = new self();
        $list->logos = array_map(
            function ($data) use ($propertyPath) {
                return Logo::deserialise($data, $propertyPath . '[]');
            },
            $data
        );

        return $list;
    }

    /**
     * @param Logo[] $logos
     */
    public function __construct(array $logos = [])
    {
        Assert::allIsInstanceOf($logos, Logo::class);

        $this->logos = $logos;
    }
}
