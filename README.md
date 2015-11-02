[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SURFnet/conext-operations-support/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/SURFnet/conext-operations-support/?branch=develop)
[![Build Status](https://travis-ci.org/SURFnet/conext-operations-support.svg?branch=develop)](https://travis-ci.org/SURFnet/conext-operations-support)

# Conext Operations Support

## Environment

 * PHP CLI ^5.6 with cURL, mcrypt, GMP, PDO MySQL extensions
 * MariaDB ^10

## Building

Required: PHP CLI ^5.6

 * Checkout a tag or download a tarball from GitHub
 * Run `composer install --no-dev --ignore-platform-reqs`
 * Configure application parameters by copying parameters.yml.dist to parameters.yml and modifying the values
   according to the instructions in the file.

## Running suites

```console
$ # Run all suites using the JIRA reporter
$ app/console operations-support:suites:run -vvv --reporter=jira
$ # Run specific suites using the CLI reporter
$ app/console operations-support:suites:run -vvv --suites=foo_suite,bar_suite
```

## Blacklisting entities for certain or all tests

```yml
# app/config/suites.yml
surfnet_conext_operations_support:
    blacklist:
        '*':
            - ['https://ministerievandefensie.invalid/saml/metadata', 'idp']
        foo_suite:
            - ['https://onegini.invalid/saml/metadata', 'idp']
        foo_suite.bar_test:
            - ['https://docs.invalid', 'sp']
```

## Test suite development

The verification suites should all reside in the `Surfnet\VerificationSuite` namespace, this is located in the
 `src/Surfnet/VerificationSuite` folder.

### Creating a new Suite

1. Create a new Suite namespace in the `Surfnet\VerificationSuite` namespace, e.g. `Surfnet\VerificationSuite\ExampleSuite`
2. Create a new Suite class, e.g. `Surfnet\VerificationSuite\ExampleSuite\ExampleSuite`. It is required to name the Suite
class the same as the suite namespace. The Suite will be identified by its namespace
 in the logs.
3. Have your new Suite extend the `Surfnet\Conext\EntityVerificationFramework\Suite`. This takes care of all the heavy
 lifting. All you have to do is implement two methods:
    - `shouldBeSkipped(VerificationContext $verificationContext) : bool`: to determine if the suite can run.
    - `getReasonToSkip() : string`: if the suite is skipped, the returned string is logged as the reason why.
4. Add the suite to the suites configuration file:

```diff
 # app/config/suites.yml
 surfnet_conext_operations_support:
     suites:
+        your_suite: []
```

### Creating a new Test

1. Create a new Test in the correct namespace, e.g. `Surfnet\VerificationSuite\ExampleSuite\Test\ExampleTest`. All tests
 within a suite must be placed in the `Test` folder within the suite namespace
2. Have your new suite implement the `Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest` interface. This
 requires the implementation of three methods:
    - `shouldBeSkipped(VerificationContext $verificationContext) : bool`: to determine if the test can run.
    - `getReasonToSkip() : string`: if the test is skipped, the returned string is logged as the reason why.
    - `verify(VerificationContext $verificationContext) : TestResult`: the actual test logic should be placed here. The
     return value must be an implementation of `Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult`;
     easiest is to just use the `Surfnet\Conext\EntityVerificationFramework\TestResult` object. It has two factory
     methods: `success()` for when the test passed successfully and `failed($reason, $explanation, $severity)`. The
     `$reason` is a short, descriptive, single sentence reason as to why the test failed. The `$explanation` is what
     actually caused the test to fail. This can be as in detail as required to fix the issue. The `$severity` should be
     one of five levels, available as constants on the `TestResult` class.
4. Add the test to the suites configuration file:

```diff
 # app/config/suites.yml
 surfnet_conext_operations_support:
     suites:
-        foo_suite: []
+        foo_suite:
+             - bar_test
```

## Development

### Requirements

- [Vagrant][1]
- [Vagrant VBGuest plugin][2]
- [Vagrant Hosts Updated plugin][3]
- [Ansible][4]
- [Composer][5]

### Getting Started

1. Checkout the project from Github: `git clone git@github.com:SURFnet/conext-operations-support.git && cd conext-operations-support`
2. Create the Vagrant machine with `vagrant up`, this may take a few minutes
3. Install all vendors with a `composer install`
4. Get to work ;)

[1]: https://www.vagrantup.com/
[2]: https://github.com/dotless-de/vagrant-vbguest
[3]: https://github.com/cogitatio/vagrant-hostsupdater
[4]: http://www.ansible.com/
[5]: https://getcomposer.org/
