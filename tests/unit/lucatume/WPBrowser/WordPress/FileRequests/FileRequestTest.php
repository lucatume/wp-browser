<?php

namespace Unit\lucatume\WPBrowser\WordPress\FileRequests;

use Codeception\Test\Unit;
use lucatume\WPBrowser\Tests\Traits\Fork;
use lucatume\WPBrowser\Tests\Traits\TmpFilesCleanup;
use lucatume\WPBrowser\Utils\Filesystem as FS;
use lucatume\WPBrowser\WordPress\FileRequests\FileGetRequest;

class FileRequestTest extends Unit
{
    use TmpFilesCleanup;

    /**
     * The target file is required inside FileRequest::execute(), a method scope. WordPress admin
     * entry scripts build `$menu`/`$submenu`/`$compat` in the including scope without a `global`
     * declaration, while a later include (WP 7.0 `wp-admin/includes/menu.php`) declares them
     * `global` before `uksort()`. Without the menu globals being bound to the global scope before
     * the require, that rebind hits an empty global and `uksort(null)` fatals on PHP 8.
     *
     * @test
     */
    public function it_keeps_admin_menu_globals_global_across_required_includes(): void
    {
        $dir = FS::tmpDir('file_request_', [
            // Mimics wp-admin/admin.php -> wp-admin/menu.php: builds $menu without `global`.
            'entry.php' => <<<'PHP'
<?php
$menu[10] = ['Ten'];
$menu[2]  = ['Two'];
require __DIR__ . '/includes.php';
PHP
,
            // Mimics WP 7.0 wp-admin/includes/menu.php: declares the globals, then sorts.
            'includes.php' => <<<'PHP'
<?php
global $menu, $submenu, $compat;
uksort($menu, 'strnatcasecmp');
PHP
,
        ]);

        $request = new FileGetRequest('localhost', '/wp-admin/admin.php', $dir . '/entry.php', ['a' => '1']);
        $request->addAfterLoadClosure(static function () : array {
            return array_keys($GLOBALS['menu'] ?? []);
        });

        $result = Fork::executeClosure(static function () use ($request) : array {
            return $request->execute();
        });

        $this->assertSame([[2, 10]], $result);
    }
}
