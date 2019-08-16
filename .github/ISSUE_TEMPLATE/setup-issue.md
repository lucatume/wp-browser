---
name: Setup issue
about: You've encountered an issue while trying to set up tests
title: "[SETUP ISSUE]"
labels: ''
assignees: ''

---

**Environment**
OS: [e.g. Windows, Mac, Linux]
PHP version: [e.g. 7.1, 5.6]
Installed Codeception version: [e.g. 2.5.0]
Installed wp-browser version: [e.g. 2.2.1]
WordPress version: [e.g. 5.4]
Local development environment: [e.g. PHP built-in server, Valet, MAMP, Local by Flywheel, Docker]
WordPress structure and management: [e.g. default, Bedrock, other]

**Did you use the `codecept init wpbrowser` command?**
The bootstrap command is the recommended way to scaffold tests.

**Did you take a look at Codeception and wp-browser documentation?**
Codeception documentation can be found [here](https://codeception.com/docs/01-Introduction), wp-browser documentation can be found [here](https://wpbrowser.wptestkit.dev/).

**Codeception configuration file**
If you were able to complete the setup then paste, in a fenced YAML block, the content of your Codeception configuration file; remove any sensitive data!

```yaml
actor: Tester
paths:
    tests: tests
    log: tests/_output
    data: tests/_data
    helpers: tests/_support
settings:
    bootstrap: _bootstrap.php
    colors: true
    memory_limit: 1024M
params:
    - .env.testing
```

**Suite configuration file**
If you're encountering an issue with a specific suite, please provide its configuration file.

```yaml
# suite for acceptance tests.
# perform tests in browser using the WebDriver or PhpBrowser.
# If you need both WebDriver and PHPBrowser tests - create a separate suite.

class_name: AcceptanceTester
modules:
    enabled:
        - WPBrowser
        - WPDb
        - AcceptanceHelper
        - WPFilesystem
    config:
        WPBrowser:
            url: '%WP_URL%'
            adminUsername: 'admin'
            adminPassword: 'admin'
            adminPath: '/wp-admin'
        WPDb:
            dsn: 'mysql:host=%DB_HOST%;dbname=%DB_NAME%'
            user: %DB_USER%
            password: %DB_PASSWORD%
            dump: 'tests/_data/dump.sql'
            populate: true
            cleanup: true
            reconnect: false
            url: '%WP_URL%'
            tablePrefix: 'wp_'
        WPFilesystem:
            wpRootFolder: '%WP_ROOT_FOLDER%'
            themes: '/wp-content/themes'
            plugins: '/wp-content/plugins'
            mu-plugins: '/wp-content/mu-plugins'
            uploads: '/wp-content/uploads'
```

**Describe the issue you're encountering**
A clear and concise description of what the problem is.

**Output**
If applicable paste here the output of the command that's causing the issue

**To Reproduce**
Steps to reproduce the behavior.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Additional context**
Add any other context about the problem here.
