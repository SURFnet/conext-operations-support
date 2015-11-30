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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Error;

use PHPUnit_Framework_TestCase as TestCase;
use Mockery as m;
use Mockery\MockInterface;
use Surfnet\Conext\EntityVerificationFramework\Error\ErrorMessageCollector;

class ErrorMessageCollectorTest extends TestCase
{
    /**
     * @test
     * @group Error
     * @dataProvider errors
     *
     * @param string[] $errors
     */
    public function it_collects_error_messages(array $errors)
    {
        $collector = new ErrorMessageCollector();
        $messages = $collector->collectDuring(function () use ($errors) {
            foreach ($errors as $error) {
                trigger_error($error, E_USER_ERROR);
            }
        });

        $this->assertEquals($errors, $messages);
    }

    public function errors()
    {
        return [
            '0 errors' => [array()],
            '1 error'  => [array('My first error')],
            '2 errors' => [array('My second error', 'My third error')],
        ];
    }
}
