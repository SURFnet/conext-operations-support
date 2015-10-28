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

namespace Surfnet\JiraApiClientBundle;

use Jira_Api as JiraApiClient;
use Jira_Api_Result as ApiResult;

final class ApiClient extends JiraApiClient
{
    /**
     * @param string $issueKey
     * @param string $commentId
     * @return ApiResult|false
     */
    public function getComment($issueKey, $commentId)
    {
        return $this->api(
            self::REQUEST_GET,
            sprintf('/rest/api/2/issue/%s/comment/%s', urlencode($issueKey), urlencode($commentId))
        );
    }
}
