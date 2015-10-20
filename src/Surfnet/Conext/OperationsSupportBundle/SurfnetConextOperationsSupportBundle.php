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

namespace Surfnet\Conext\OperationsSupportBundle;

use Surfnet\Conext\OperationsSupportBundle\Value\JiraIssueStatus;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SurfnetConextOperationsSupportBundle extends Bundle
{
    public function boot()
    {
        /** @var JiraIssueStatus $mutedStatus */
        $mutedStatus = $this->container->get('surfnet_conext_operations_support.value.muted_jira_status');
        JiraIssueStatus::configureMutedStatus($mutedStatus);
    }
}
