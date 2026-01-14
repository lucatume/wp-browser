# Teamplay Specification

## Overview

Teamplay is a Codeception CLI command that bridges Playwright and wp-browser, allowing Playwright tests to access WordPress backend state (database, filesystem) through wp-browser's existing modules.

Modern WordPress development increasingly relies on JavaScript-driven interfaces, particularly the Block Editor (Gutenberg). Playwright is the industry-standard tool for testing such applications—it's what the WordPress Block Editor team uses, and it comes with extensive documentation, tooling, and community support.

However, Playwright operates purely in the browser and cannot directly verify or manipulate backend state. This is where wp-browser and Codeception excel: database queries, filesystem operations, WordPress option manipulation, and other server-side concerns.

Teamplay allows both technologies to do what they do best:
- **Playwright**: Browser automation, JavaScript-driven UI testing, visual assertions
- **wp-browser + Codeception**: Database state verification, filesystem checks, WordPress-specific backend operations

## Goals

- Enable Playwright tests to call wp-browser module methods (WPDb, WPFilesystem, etc.)
- Maintain familiar wp-browser API patterns in JavaScript/TypeScript
- Integrate seamlessly with existing Playwright workflows and tooling
- Support the same test lifecycle hooks (`_before`/`_after`) that Codeception tests use

## Non-Goals

- **Parallel test orchestration**: Teamplay does not manage test parallelization. Whether tests can run in parallel depends on the suite's module configuration (e.g., `WPDb` with `cleanup: true` prevents parallelization; `cleanup: false` allows it). This is the tester's responsibility to configure correctly.
- **WordPress multisite handling**: Multisite support is handled by the underlying Codeception modules (WPDb, WPLoader, etc.), not by Teamplay itself.
- **Configuration hand-holding**: Users are expected to understand their suite's module configuration. Teamplay exposes whatever methods the suite's modules provide—it does not validate or guide users through complex setups.
- **New testing abstractions**: Teamplay does not introduce new testing patterns or fixtures. It simply bridges existing wp-browser module methods to Playwright.

## User Experience

### Example Playwright Specs

#### Scenario 1: Plugin Activation/Deactivation
A test where the user:
1. Logs in as admin
2. Goes to the plugins page
3. Activates a plugin and sees no errors, plugin shows as activated
4. Verifies plugin activation state by checking `active_plugins` option in the database
5. Goes back to plugins page
6. Deactivates the plugin and sees no errors, plugin shows as deactivated
7. Verifies plugin deactivation state by checking `active_plugins` option in the database

```typescript
import { test, expect } from '../teamplay';

test('plugin activation and deactivation', async ({ page, teamplay }) => {
  // Login as admin
  await page.goto('/wp-login.php');
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'password');
  await page.click('#wp-submit');

  // Go to plugins page and activate
  await page.goto('/wp-admin/plugins.php');
  await page.click('[data-slug="my-plugin"] .activate a');

  // Verify no errors visible and plugin shows activated
  await expect(page.locator('.error')).not.toBeVisible();
  await expect(page.locator('[data-slug="my-plugin"] .deactivate')).toBeVisible();

  // Verify in database
  const activePlugins = await teamplay.grabOptionFromDatabase('active_plugins');
  expect(activePlugins).toContain('my-plugin/my-plugin.php');

  // Deactivate plugin
  await page.click('[data-slug="my-plugin"] .deactivate a');

  // Verify no errors and plugin shows deactivated
  await expect(page.locator('.error')).not.toBeVisible();
  await expect(page.locator('[data-slug="my-plugin"] .activate')).toBeVisible();

  // Verify in database
  const activePluginsAfter = await teamplay.grabOptionFromDatabase('active_plugins');
  expect(activePluginsAfter).not.toContain('my-plugin/my-plugin.php');
});
```

#### Scenario 2: Image Upload with Filesystem Verification
A test where the user:
1. Uploads an image through the WordPress admin
2. Verifies the image file exists in the uploads directory
3. Verifies the image was processed into expected formats/sizes

```typescript
import { test, expect } from '../teamplay';

test('image upload creates expected files', async ({ page, teamplay }) => {
  // Login as admin
  await page.goto('/wp-login.php');
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'password');
  await page.click('#wp-submit');

  // Go to media upload page
  await page.goto('/wp-admin/media-new.php');

  // Upload an image
  await page.setInputFiles('input[type="file"]', 'tests/_data/test-image.jpg');

  // Wait for upload to complete
  await page.waitForSelector('.media-item .percent', { state: 'hidden' });

  // Verify original file exists in uploads directory
  await teamplay.seeUploadedFileFound('test-image.jpg');

  // Verify WordPress generated thumbnail sizes
  await teamplay.seeUploadedFileFound('test-image-150x150.jpg');
  await teamplay.seeUploadedFileFound('test-image-300x200.jpg');
});
```

## Architecture

### Teamplay Fixture

All public methods from all modules enabled in the suite are exposed through a `teamplay` fixture. This includes all method types: `grab*`, `see*`, `dontSee*`, `have*`, and any other public methods.

**Method filtering (v1):** Methods are only exposed if all their parameters accept simple types:
- Scalars (string, int, float, bool)
- Arrays
- `stdClass` objects

**Edge cases:**
- **Union types** (e.g., `string|array`): Included if ANY type in the union is simple. For example, `string|WP_Post` is included because `string` is simple.
- **Nullable types** (e.g., `?string`): Included if the base type is simple. `?string` is included; `?WP_Post` is excluded.
- **Variadic parameters** (e.g., `string ...$args`): Included if the type is simple.
- **No type hint**: Included. Users are responsible for passing appropriate values.

Methods where ALL parameter types are complex objects (e.g., `WP_Post`, custom classes with no simple alternative) are excluded from the `teamplay` fixture.

Users are responsible for configuring their suite with modules that make sense for the Playwright context.

Tests opt-in to Teamplay by importing `test` from the generated fixture file instead of `@playwright/test`:

```typescript
// Plain Playwright test - no Teamplay
import { test, expect } from '@playwright/test';

test('basic UI test', async ({ page }) => {
  // Standard Playwright test without PHP backend access
});
```

```typescript
// Teamplay-powered test
import { test, expect } from '../teamplay';

test('test with database verification', async ({ page, teamplay }) => {
  const option = await teamplay.grabOptionFromDatabase('option_name');
  // ...
});
```

Both types of tests can coexist in the same `e2e/` directory.

Example methods available on the `teamplay` fixture:
- `teamplay.grabOptionFromDatabase('option_name')` - from WPDb module
- `teamplay.seeUploadedFileFound('filename.jpg')` - from WPFilesystem module
- Any method from any other module in the suite

### Build-Time Generation

The list of methods available on the `teamplay` namespace is built by the `codecept teamplay:build` command and is per-suite. This means different suites can have different methods available based on which modules they have enabled.

Method name conflicts between modules are handled by Codeception's existing module loading process, which will fail if two modules define methods with the same name.

**Generated files:**
- `playwright.config.ts` (or `.js`) - Playwright configuration extending the root config
- `teamplay.ts` - The fixture file with the `teamplay` client and custom `test` export
- `teamplay.d.ts` - TypeScript type definitions for all exposed methods

The TypeScript definitions provide full autocomplete and type checking in IDEs:

```typescript
// Generated teamplay.d.ts (example)
export interface TeamplayClient {
  grabOptionFromDatabase(optionName: string): Promise<unknown>;
  seeUploadedFileFound(filename: string): Promise<void>;
  haveUserInDatabase(userData: {
    user_login: string;
    user_pass: string;
    user_email?: string;
    // ...
  }): Promise<number>;
  // ... all other module methods
}
```

### Per-Suite Playwright Configuration

For each suite that uses Teamplay, a Playwright configuration file is generated by `codecept teamplay:build`:
- File name: `playwright.config.js` or `playwright.config.ts` (matching the extension of the root Playwright config)
- Location: In the suite directory (e.g., `tests/{suite}/playwright.config.ts`)

The generated config:
1. Imports and extends the root project Playwright configuration
2. Defines the `teamplay` global object
3. Defines all available functions in the `teamplay` namespace for that suite

Example file structure after running `codecept teamplay:build acceptance`:
```
tests/
└── acceptance/
    ├── playwright.config.ts  # Generated by teamplay:build
    ├── teamplay.ts           # Generated fixture file
    ├── AcceptanceCest.php
    └── e2e/
        ├── plugin-activation.spec.ts  # Uses teamplay
        └── basic-ui.spec.ts           # Plain Playwright test
```

### Generated Fixture File

The `teamplay.ts` file is generated at build time and contains:
1. The `teamplay` client with all methods from suite modules
2. A custom `test` export that extends Playwright's base test
3. A fixture that handles `_before`/`_after` lifecycle hooks (only runs when a test uses the `teamplay` fixture)

Example structure:
```typescript
import { test as base, expect } from '@playwright/test';

const teamplayUrl = () => `http://localhost:${process.env.TEAMPLAY_PORT}`;

const teamplayClient = {
  async grabOptionFromDatabase(optionName: string) {
    const response = await fetch(`${teamplayUrl()}/call`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ method: 'grabOptionFromDatabase', args: [optionName] }),
    });
    const result = await response.json();
    if (result.error) throw new Error(result.error);
    return result.value;
  },
  // ... other methods generated based on suite modules
};

export const test = base.extend<{ teamplay: typeof teamplayClient }>({
  teamplay: async ({}, use, testInfo) => {
    const testMeta = {
      test: {
        title: testInfo.title,
        file: testInfo.file,
      }
    };
    await fetch(`${teamplayUrl()}/_before`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(testMeta),
    });
    await use(teamplayClient);
    await fetch(`${teamplayUrl()}/_after`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(testMeta),
    });
  },
});

export { expect };
```

### Communication Layer

When a Playwright test calls a `teamplay` method (e.g., `await teamplay.grabOptionFromDatabase('active_plugins')`):

1. The JavaScript function makes an HTTP request to a local PHP server
2. The PHP server executes the corresponding Codeception module method
3. The result is serialized using the Packer and returned to JavaScript
4. The JavaScript function deserializes the result and returns it

#### Serialization Format (Packer)

Data is exchanged using base64-encoded JSON to avoid mangling of slashes and quotes. The JSON format uses typed wrappers that allow for parsing and reconstruction on both sides.

**Supported types:**
- **Primitives**: `integer`, `float`, `boolean`, `string`, `null`
- **Arrays**: Sequential and associative arrays with nested type information
- **Objects**: Full object serialization including private/protected properties, with circular reference support via `@ref` markers
- **Closures**: Serialized as `null` on the JavaScript side (JavaScript cannot execute PHP closures)
- **Resources**: Serialized as `null` (resources cannot cross process boundaries)
- **Exceptions**: Full exception details with sanitized stack traces (objects and args removed from trace entries)

**Example packed integer:**
```json
{"type": "integer", "value": 42}
```

**Example packed object with circular reference:**
```json
{
  "type": "object",
  "value": {
    "@class": "stdClass",
    "@ref": "@ref_0",
    "name": {"type": "string", "value": "test"},
    "self": {"type": "reference", "value": "@ref_0"}
  }
}
```

**JavaScript Packer:**
A JavaScript version of the Packer handles deserialization on the Playwright side. This file:
- Location: `{support_dir}/Teamplay/packer.js` (or `packer.ts` if the root Playwright config is TypeScript), where `{support_dir}` is read from `Codeception\Configuration::config()['paths']['support']`
- Created once by `teamplay:build` if it doesn't exist (not regenerated on subsequent builds)
- Implements the same unpacking logic as the PHP version
- Throws a standard `Error` if unpacking fails, with a descriptive message (e.g., "Packer: unknown type 'foo'", "Packer: invalid reference '@ref_99'")
- Handles circular references via `@ref` markers with the same logic as the PHP version

The generated `teamplay.ts` fixture imports the Packer from this location.

The API server port is communicated via Playwright's `webServer` stdout detection:
1. The Teamplay server outputs `Teamplay server running on port XXXX` to stdout when ready
2. Playwright's `webServer` config uses a regex like `/Teamplay server running on port (?<teamplay_port>\d+)/` to extract the port and set `process.env.TEAMPLAY_PORT`
3. The `teamplay` global reads `process.env.TEAMPLAY_PORT` to construct request URLs

### Error Handling

When a PHP method throws an exception or an assertion fails:
1. The PHP server returns an error response with the exception details
2. The JavaScript `teamplay` method throws an Error
3. Playwright catches the error and reports it as a test failure

This ensures that PHP-side failures are properly surfaced in Playwright test results.

**Server unavailability:** If the PHP server crashes or becomes unresponsive, the test fails with a clear error message (e.g., "Teamplay server unavailable"). No automatic restart is attempted.

### Timeouts

Timeouts are handled at two levels:

1. **PHP side (primary)**: Individual module methods may have their own timeouts configured through their Codeception module configuration. This is the source of truth for operation-specific timeouts.

2. **JavaScript side (safety net)**: Each HTTP request from the `teamplay` client has a default timeout of 30 seconds. If this timeout is exceeded, the test fails. This prevents tests from hanging indefinitely if something goes wrong on the PHP side.

The JavaScript timeout is configurable (see Configuration section).

### Server Lifecycle

Playwright orchestrates the server lifecycle using its `webServer` configuration:
1. User runs `npx playwright test` (or similar Playwright command)
2. Playwright starts the Teamplay PHP server via `vendor/bin/codecept teamplay:serve {suite}`
3. The server initializes all modules and calls `_beforeSuite` on each module (same as Codeception suite startup)
4. Tests run with access to the `teamplay` global
5. When Playwright shuts down the server, `_afterSuite` is called on each module before exit

This means the server lifetime corresponds to a single suite run, with suite-level hooks (`_beforeSuite`/`_afterSuite`) executed at server start/stop, and test-level hooks (`_before`/`_after`) executed via API calls for each test.

### Test Lifecycle Hooks

Tests that use the `teamplay` fixture automatically coordinate with the Teamplay server to execute module lifecycle hooks:

- **Before each test**: All modules execute their `_before` method
- **After each test**: All modules execute their `_after` method

This coordination is transparent to the test writer - it happens automatically when the test destructures `teamplay` from the test arguments. Tests that don't use the `teamplay` fixture skip these hooks entirely.

### File Structure

Playwright test files live inside the Codeception suite directory:

```
tests/
└── {suite-name}/
    └── e2e/
        ├── plugin-activation.spec.ts
        ├── image-upload.spec.js
        └── ...
```

Files can be either `.spec.ts` (TypeScript) or `.spec.js` (JavaScript).

### Running Tests

Users run Playwright tests using Playwright:

```bash
npx playwright test --config=tests/{suite}/playwright.config.ts
```

Playwright's `webServer` configuration handles starting the Teamplay server automatically.

If the suite also contains PHP tests (Cest/Cept files), they are run separately:

```bash
vendor/bin/codecept run {suite}
```

PHP tests and Playwright tests are independent and can be run in any order.

## Requirements

- **PHP**: ^8.0 (same as wp-browser)
- **Playwright**: No specific minimum version required

## Configuration

### Teamplay Command

Teamplay is a CLI command, not a module. It reads the suite's existing module configuration from Codeception's runtime configuration (`Codeception\Configuration::config()`).

```bash
vendor/bin/codecept teamplay:serve <suite> [options]
```

**Arguments:**
- `<suite>` - The name of the Codeception suite whose modules should be exposed (e.g., `acceptance`)

**Options:**
- `--port=<port>` - Fixed port for the API server. If not specified, a port is auto-assigned
- `--timeout=<ms>` - JavaScript-side request timeout in milliseconds. Defaults to 30000 (30 seconds)
- `--log=<path>` - Path to a log file for server output
- `--verbose` or `-v` - Enable verbose output for debugging

**Implementation:**
The server is implemented in PHP using PHP's built-in web server (`php -S`). The command correctly outputs to stdout and stderr to allow redirection by Playwright or other tools.

**Port handling:** If no `--port` is specified, a random available port is selected. If the selected or specified port is already in use, the server fails immediately with a clear error message (e.g., "Port 3000 is already in use").

### Teamplay Build Command

Generates the Playwright configuration and TypeScript files for a suite:

```bash
vendor/bin/codecept teamplay:build <suite> --playwright-config=<path>
```

This generates:
- `tests/<suite>/playwright.config.ts` - Extends the root Playwright config
- `tests/<suite>/teamplay.ts` - The fixture file with the `teamplay` client
- `tests/<suite>/teamplay.d.ts` - TypeScript type definitions

**Version control:** Generated files should be committed to VCS. This ensures CI environments don't need to run the build command before running tests, and provides visibility into what methods are available.

**Automatic regeneration:** The `teamplay:serve` command automatically checks if generated files are stale before starting the server:
1. An MD5 hash of the suite's runtime configuration (from `Codeception\Configuration::config()`) is stored in a `.teamplay-hash` file alongside the generated files
2. At server start, the current configuration hash is compared to the stored hash
3. If the hashes differ (modules added/removed, configuration changed), files are automatically regenerated
4. This ensures the generated files always match the current suite configuration

Users can also run `teamplay:build` manually to regenerate files without starting the server.

### Playwright webServer Configuration

The generated Playwright config includes a `webServer` configuration that:
- Runs `vendor/bin/codecept teamplay:serve {suite}` to start the API server
- Detects server readiness via stdout (the server outputs the URL when ready)
- Shuts down the server when tests complete

Example generated webServer config:
```typescript
webServer: {
  command: 'vendor/bin/codecept teamplay:serve acceptance',
  reuseExistingServer: !process.env.CI,
  wait: {
    stdout: '/Teamplay server running on port (?<teamplay_port>\\d+)/'
  },
}
```

The server outputs `Teamplay server running on port XXXX` to stdout when ready. Playwright's `wait.stdout` captures the port via the named regex group `teamplay_port` and makes it available as `process.env.TEAMPLAY_PORT` for use in tests.

See [Playwright webServer documentation](https://playwright.dev/docs/api/class-testconfig#test-config-web-server) for details on the `wait.stdout` pattern.

### Playwright Execution Options

When running Playwright tests, the following options are recommended:

- `--workers 1`: To ensure tests run serially, not in parallel

Other Playwright options should be configured in the suite's generated Playwright configuration file, which extends the root config.

## API

The Teamplay PHP server exposes the following HTTP endpoints:

### `GET /health`

Health check endpoint to verify the server is running and ready.

**Response (200 OK):**
```json
{"status": "ok"}
```

### `GET /methods`

Returns a list of all available methods exposed by the suite's modules.

**Response (200 OK):**
```json
{
  "methods": [
    {
      "name": "grabOptionFromDatabase",
      "module": "WPDb",
      "parameters": [
        {"name": "optionName", "type": "string", "required": true}
      ]
    },
    {
      "name": "seeUploadedFileFound",
      "module": "WPFilesystem",
      "parameters": [
        {"name": "filename", "type": "string", "required": true}
      ]
    }
  ]
}
```

### `POST /_before`

Triggers the `_before` hook on all enabled modules. Called automatically before each Playwright test.

**Request body:**
```json
{
  "test": {
    "title": "plugin activation and deactivation",
    "file": "tests/acceptance/e2e/plugin-activation.spec.ts"
  }
}
```

The test metadata is used to construct a minimal `TestInterface` instance required by Codeception module `_before` methods. This is a simple implementation that only provides the `title` and `file` properties - sufficient for module lifecycle hooks.

**Response (200 OK):**
```json
{"status": "ok"}
```

**Error Response (500 Internal Server Error):** If a module's `_before` hook throws an exception.

### `POST /_after`

Triggers the `_after` hook on all enabled modules. Called automatically after each Playwright test.

**Request body:**
```json
{
  "test": {
    "title": "plugin activation and deactivation",
    "file": "tests/acceptance/e2e/plugin-activation.spec.ts"
  }
}
```

**Response (200 OK):**
```json
{"status": "ok"}
```

**Error Response (500 Internal Server Error):** If a module's `_after` hook throws an exception.

### `POST /call`

Invokes a module method with the provided arguments.

**Request body:**
```json
{
  "method": "grabOptionFromDatabase",
  "args": ["active_plugins"]
}
```

**Success Response (200 OK):**
```json
{
  "ok": true,
  "value": "<base64-encoded packed result>"
}
```

**Error Response (400 Bad Request):** If the method name is missing or invalid.
```json
{
  "ok": false,
  "error": {
    "message": "Method not found: invalidMethod",
    "type": "InvalidArgumentException"
  }
}
```

**Error Response (500 Internal Server Error):** If the method throws an exception.
```json
{
  "ok": false,
  "error": {
    "message": "Option not found: invalid_option",
    "type": "Exception",
    "trace": "<base64-encoded packed exception>"
  }
}
```