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

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;

final class JiraIssuePriority
{
    /**
     * @var int[] VerificationTestResult::SEVERITY_* indexed by their JIRA priority ID counterparts (string).
     */
    private static $prioritySeverityMap;

    /**
     * @var string
     */
    private $priorityId;

    /**
     * @param int[] $prioritySeverityMap
     */
    public static function configure(array $prioritySeverityMap)
    {
        if (self::$prioritySeverityMap !== null) {
            throw new LogicException('JIRA issue priority/severity map has already been configured');
        }

        if (array_diff($prioritySeverityMap, VerificationTestResult::VALID_SEVERITIES)
                !== array_diff(VerificationTestResult::VALID_SEVERITIES, $prioritySeverityMap)) {
            throw new InvalidArgumentException('All test failure severities must be mapped to a JIRA priority');
        }

        $priorityIds = array_map('strval', array_keys($prioritySeverityMap));
        Assert::allInArray(
            $prioritySeverityMap,
            VerificationTestResult::VALID_SEVERITIES,
            'Severities must be valid VerificationTestResult::SEVERITY_* constants, got "%s"'
        );
        Assert::allRegex($priorityIds, '~^\d+$~', 'Priority IDs must consist of one or more digits, got "%s"');

        self::$prioritySeverityMap = $prioritySeverityMap;
    }

    /**
     * @param string $priorityId
     * @return JiraIssuePriority
     */
    public static function forPriority($priorityId)
    {
        Assert::regex($priorityId, '~^\d+$~', 'Priority ID must consist of one or more digits, got "%s"');
        Assert::inArray(
            $priorityId,
            self::getValidPriorityIds(),
            "Given priority ID doesn't map to a severity"
        );

        return new JiraIssuePriority($priorityId);
    }

    /**
     * @param string $priorityId
     * @return bool
     */
    public static function hasMappingToSeverity($priorityId)
    {
        Assert::regex($priorityId, '~^\d+$~', 'Priority ID must consist of one or more digits, got "%s"');

        return array_key_exists($priorityId, self::getPrioritySeverityMap());
    }

    /**
     * @param int $severity
     * @return JiraIssuePriority
     */
    public static function forSeverity($severity)
    {
        Assert::inArray(
            $severity,
            VerificationTestResult::VALID_SEVERITIES,
            'Severity must be valid VerificationTestResult::SEVERITY_* constants, got "%s"'
        );

        return new JiraIssuePriority((string) array_search($severity, self::getPrioritySeverityMap(), true));
    }

    /**
     * @param string $priorityId
     */
    public function __construct($priorityId)
    {
        Assert::regex($priorityId, '~^\d+$~', 'Priority ID must consist of one or more digits, got "%s"');

        $this->priorityId = $priorityId;
    }

    /**
     * @return bool
     */
    public function isMappedToASeverity()
    {
        return array_key_exists($this->priorityId, self::$prioritySeverityMap);
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

    /**
     * @return int[] VerificationTestResult::SEVERITY_* indexed by their JIRA priority ID counterparts (string).
     */
    private static function getPrioritySeverityMap()
    {
        if (self::$prioritySeverityMap === null) {
            throw new LogicException('JiraIssuePriority has not yet been configured');
        }

        return self::$prioritySeverityMap;
    }

    /**
     * @return string[]
     */
    private static function getValidPriorityIds()
    {
        return array_map('strval', array_keys(self::getPrioritySeverityMap()));
    }
}
