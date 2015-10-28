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
use Surfnet\JiraApiClientBundle\Dto\Comment;

final class JiraIssueComment
{
    /**
     * @var string
     */
    private $body;

    public static function fromCommentDto(Comment $dto)
    {
        $comment = new JiraIssueComment();
        $comment->body = $dto->body;

        return $comment;
    }

    /**
     * @param string $body
     * @return bool
     */
    public function bodyEquals($body)
    {
        Assert::string($body, 'Summary must be a string');

        return str_replace("\r", '', $this->body) === $body;
    }
}
