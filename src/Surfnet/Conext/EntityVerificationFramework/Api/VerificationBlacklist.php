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

namespace Surfnet\Conext\EntityVerificationFramework\Api;

use Surfnet\Conext\EntityVerificationFramework\Value\Entity;

/**
 * Describes a blacklist that blacklists entities for specific suites or suite tests by their name.
 *
 * @see \Surfnet\Conext\EntityVerificationFramework\NameResolver
 */
interface VerificationBlacklist
{
    /**
     * @param Entity $entity
     * @param string $suiteOrTestName
     * @return boolean
     */
    public function isBlacklisted(Entity $entity, $suiteOrTestName);
}
