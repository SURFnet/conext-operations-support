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

namespace Surfnet\JiraApiClientBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\JiraApiClientBundle\DependencyInjection\Configuration;
use Surfnet\JiraApiClientBundle\Tests\DataProvider\DataProvider;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;
    use DataProvider;

    private $validConfig = [
        'base_url' => 'http://api.invalid',
        'username' => 'test user',
        'password' => 'test password',
        'project_id' => '10000',
        'default_assignee' => 'default assignee',
    ];

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider nonStringScalarProvider
     */
    public function base_url_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config['base_url'] = $value;

        $this->assertConfigurationIsInvalid([$config], 'The JIRA API base URL should be a string');
    }

    /**
     * @test
     * @group JiraApiClientBundle
     */
    public function base_url_has_to_be_valid_url()
    {
        $config = $this->validConfig;
        $config['base_url'] = 'not a valid URL';

        $this->assertConfigurationIsInvalid([$config], 'The JIRA API base URL should be a valid URL');
    }

    /**
     * @test
     * @group JiraApiClientBundle
     */
    public function base_url_cannot_contain_url_path()
    {
        $config = $this->validConfig;
        $config['base_url'] = 'http://www.this.url/has/a/path';

        $this->assertConfigurationIsInvalid([$config], 'The JIRA API base URL should not contain a path');
    }

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider nonStringScalarProvider
     */
    public function username_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["username"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The JIRA API username should be a string");
    }

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider nonStringScalarProvider
     */
    public function password_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["password"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The JIRA API password should be a string");
    }

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider nonStringScalarProvider
     */
    public function project_id_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["project_id"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The project id should be a string");
    }

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider nonStringScalarProvider
     */
    public function default_assignee_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["default_assignee"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The default assignee should be a string");
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
