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

namespace Surfnet\Conext\OperationsSupportBundle\Value;

use Surfnet\Conext\EntityVerificationFramework\Assert;

final class JiraIssuePriority
{
    /**
     * @var string
     */
    private $priorityId;

    /**
     * @param string $priorityId
     */
    public function __construct($priorityId)
    {
        Assert::regex($priorityId, '~^\d+$~', 'Priority ID must consist of one or more digits, got "%s"');

        $this->priorityId = $priorityId;
    }

    /**
     * @param JiraIssuePriority $other
     * @return bool
     */
    public function equals(JiraIssuePriority $other)
    {
        return $this == $other;
    }

    /**
     * @return string
     */
    public function getPriorityId()
    {
        return $this->priorityId;
    }
}
