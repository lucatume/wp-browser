<?php

namespace lucatume\WPBrowser\WordPress\CodeExecution;

use Closure;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\WordPress\FileRequests\FileRequestFactory;

class CodeExecutionFactory
{
    private string $wpRootDir;
    private FileRequestFactory $requestFactory;

    public function __construct(
        string $wpRootDir,
        string $domain,
        /**
         * @var array<string,string>
         */
        private array $redirectFiles = [],
        /**
         * @var array<string,mixed>
         */
        private array $presetGlobalVars = []
    ) {
        $this->wpRootDir = rtrim($wpRootDir, '\\/');
        $this->requestFactory = new FileRequestFactory($wpRootDir, $domain);
    }

    public function toCheckIfWpIsInstalled(bool $multisite): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars);

        return (new CheckWordPressInstalledAction($request, $this->wpRootDir, $multisite))
            ->getClosure();
    }

    public function toActivatePlugin(string $plugin, bool $multisite): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars);

        return (new ActivatePluginAction($request, $this->wpRootDir, $plugin, $multisite))
            ->getClosure();
    }

    public function toSwitchTheme(string $stylesheet, bool $multisite): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars);

        return (new ThemeSwitchAction($request, $this->wpRootDir, $stylesheet, $multisite))
            ->getClosure();
    }

    public function toInstallWordPressNetwork(string $adminEmail, string $title, bool $subdomain): Closure
    {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars);

        return (new InstallNetworkAction($request, $this->wpRootDir, $adminEmail, $title, $subdomain))
            ->getClosure();
    }

    public function toInstallWordPress(
        string $title,
        string $adminUser,
        string $adminPassword,
        string $adminEmail,
        string $url
    ): Closure {
        $request = $this->requestFactory->buildGetRequest()
            ->blockHttpRequests()
            ->setRedirectFiles($this->redirectFiles)
            ->addPresetGlobalVars($this->presetGlobalVars);

        return (new InstallAction($request, $this->wpRootDir, $title, $adminUser, $adminPassword, $adminEmail, $url))
            ->getClosure();
    }
}
