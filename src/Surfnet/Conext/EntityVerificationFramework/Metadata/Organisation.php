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

final class Organisation
{
    /**
     * @var MultiLocaleString
     */
    private $name;

    /**
     * @var MultiLocaleString
     */
    private $displayName;

    /**
     * @var MultiLocaleUrl
     */
    private $applicationUrl;

    public function __construct(MultiLocaleString $name, MultiLocaleString $displayName, MultiLocaleUrl $applicationUrl)
    {
        $this->name           = $name;
        $this->displayName    = $displayName;
        $this->applicationUrl = $applicationUrl;
    }

    /**
     * @param Organisation $other
     * @return bool
     */
    public function equals(Organisation $other)
    {
        return $this->name->equals($other->name)
            && $this->displayName->equals($other->displayName)
            && $this->applicationUrl->equals($other->applicationUrl);
    }

    public function __toString()
    {
        return sprintf(
            'Organisation(name=%s, displayName=%s, applicationUrl=%s)',
            $this->name,
            $this->displayName,
            $this->applicationUrl
        );
    }
}
