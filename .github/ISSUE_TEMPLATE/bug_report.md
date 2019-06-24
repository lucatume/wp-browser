---
name: Bug report
about: You found a bug, damn.
title: "[BUG]"
labels: bug
assignees: lucatume

---

**Environment**
OS: [e.g. Windows, Mac, Linux]  
PHP version: [e.g. 7.1, 5.6]  
Installed Codeception version: [e.g. 2.5.0]  
Installed wp-browser version: [e.g. 2.2.1]  
WordPress version: [e.g. 5.4]  
Local development environment: [e.g. PHP built-in server, Valet, MAMP, Local by Flywheel, Docker]  
WordPress structure and management: [e.g. default, Bedrock, other]  

**Can you perform the test manually?**
If applicable, try to walk through the test and execute it manually: can you do it using the browser?

**Codeception configuration file**  
Paste, in a fenced YAML block, the content of your Codeception configuration file; remove any sensible data!  

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
Paste, in a fenced YAML block, the content of the suite configuration file; remove any sensible data!

```yaml
# Codeception Test Suite Configuration

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

**Describe the bug**
A clear and concise description of what the bug is.

**Output**
If applicable paste here the output of the command that's causing the issue

**To Reproduce**
Steps to reproduce the behavior.

**Expected behavior**
A clear and concise description of what you expected to happen.

**Screenshots**
If applicable, add screenshots to help explain your problem.

**Additional context**
Add any other context about the problem here.
