<?php

namespace lucatume\WPBrowser\WordPress\FileRequests;

use Closure;

class FileRequestClosureFactory
{
    private FileRequestFactory $requestFactory;

    public function __construct(FileRequestFactory $requestFactory)
    {
        $this->requestFactory = $requestFactory;
    }

    public function toInstall(
        string $title,
        string $username,
        string $password,
        string $email,
        ?string $url = null
    ): Closure {
        $request = $this->requestFactory->buildPostRequest('/wp-admin/install.php?step=2', [
            'weblog_title' => $title,
            'user_name' => $username,
            'admin_password' => $password,
            'admin_password2' => $password,
            'admin_email' => $email,
            'blog_public' => 1,
        ]);

        if ($url !== null) {
            $request->setServerVar('HTTP_HOST', $url);
        }

        // Do not send mails during the installation, avoid failures due to a missing mail function.
        $request->addPreloadClosure(static function () {
            if (!function_exists('wp_mail')) {
                function wp_mail()
                {
                    return true;
                }
            }
        });

        $request->setConstant('WP_HTTP_BLOCK_EXTERNAL', true)
            ->setPreloadFilter('block_local_requests', '__return_true');

        return static function () use ($request): void {
            $request->execute();
        };
    }
}
