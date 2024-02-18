<?php


namespace lucatume\WPBrowser\Utils;

use JsonException;
use lucatume\WPBrowser\Adapters\Symfony\Component\Process\Process;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use lucatume\WPBrowser\Utils\Filesystem as FS;

use function lucatume\WPBrowser\useMemoString;

class ChromedriverInstaller
{

<<<<<<< Updated upstream
    public const ERR_INVALID_VERSION = 1;
    public const ERR_INVALID_BINARY = 2;
    public const ERR_UNSUPPORTED_PLATFORM = 3;
    public const ERR_REMOVE_EXISTING_ZIP_FILE = 4;
    public const ERR_VERSION_NOT_STRING = 5;
    public const ERR_INVALID_VERSION_FORMAT = 6;
    public const ERR_DESTINATION_NOT_DIR = 7;
    public const ERR_FETCH_MILESTONE_DOWNLOADS = 11;
    public const ERR_DECODE_MILESTONE_DOWNLOADS = 12;
    public const ERR_DOWNLOAD_URL_NOT_FOUND = 13;
    public const ERR_REMOVE_EXISTING_BINARY = 14;
    public const ERR_MOVE_BINARY = 15;
    public const ERR_DETECT_PLATFORM = 16;
    public const ERR_BINARY_CHMOD = 17;
=======
    public const ERR_INVALID_BINARY = 1;
    public const ERR_UNSUPPORTED_PLATFORM = 2;
    public const ERR_REMOVE_EXISTING_ZIP_FILE = 3;
    public const ERR_VERSION_NOT_STRING = 4;
    public const ERR_INVALID_VERSION_FORMAT = 5;
    public const ERR_DESTINATION_NOT_DIR = 6;
    public const ERR_FETCH_MILESTONE_DOWNLOADS = 7;
    public const ERR_DECODE_MILESTONE_DOWNLOADS = 8;
    public const ERR_DOWNLOAD_URL_NOT_FOUND = 9;
    public const ERR_REMOVE_EXISTING_BINARY = 10;
    public const ERR_DETECT_PLATFORM = 11;
    public const ERR_BINARY_CHMOD = 12;

>>>>>>> Stashed changes
    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;
    /** @var 'linux64'|'mac-x64'|'mac-arm64'|'win32'|'win64' */
    private $platform;
    /**
     * @var string
     */
    private $binary;
    /**
     * @var string
     */
    private $milestone;
    /**
     * @var bool
     */
    private $useEnvZipFile = true;

    public function __construct(
        string $version = null,
        string $platform = null,
        string $binary = null,
        OutputInterface $output = null
    ) {
        $this->output = $output ?? new NullOutput();

        $platform = $platform ?? $this->detectPlatform();
        $this->platform = $this->checkPlatform($platform);

        $this->output->writeln("Platform: $platform");

        $binary = $binary ?? $this->detectBinary();
        $this->binary = $this->checkBinary($binary);

        $this->output->writeln("Binary: $binary");

        $version = $version ?? $this->detectVersion();
        $this->milestone = $this->checkVersion($version);

        $this->output->writeln("Version: $version");
    }

    /**
<<<<<<< Updated upstream
=======
     * @throws RuntimeException
     */
    private function detectPlatform(): string
    {
        // Return one of `linux64`, `mac-arm64`,`mac-x64`, `win32`, `win64`.
        $system = php_uname('s');
        $arch = php_uname('m');

        if ($system === 'Darwin') {
            if ($arch === 'arm64') {
                return 'mac-arm64';
            }

            return 'mac-x64';
        }

        if ($system === 'Linux') {
            return 'linux64';
        }

        if ($system === 'Windows NT') {
            if (strpos($arch, '64') !== false) {
                return 'win64';
            }

            return 'win32';
        }

        throw new RuntimeException('Failed to detect platform.', self::ERR_DETECT_PLATFORM);
    }

    /**
     * @return 'linux64'|'mac-x64'|'mac-arm64'|'win32'|'win64'
     *
     * @throws RuntimeException
     * @param mixed $platform
     */
    private function checkPlatform($platform): string
    {
        if (!(is_string($platform) && in_array($platform, [
                'linux64',
                'mac-arm64',
                'mac-x64',
                'win32',
                'win64'
            ]))) {
            throw new RuntimeException(
                'Invalid platform, supported platforms are: linux64, mac-arm64, mac-x64, win32, win64.',
                self::ERR_UNSUPPORTED_PLATFORM
            );
        }

        /** @var 'linux64'|'mac-arm64'|'mac-x64'|'win32'|'win64' $platform */
        return $platform;
    }

    /**
     * @throws RuntimeException
     */
    private function detectBinary(): string
    {
        switch ($this->platform) {
            case 'linux64':
                return $this->detectLinuxBinaryPath();
            case 'mac-x64':
            case 'mac-arm64':
                return '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
            case 'win32':
            case 'win64':
                return $this->detectWindowsBinaryPath();
        }
    }

    private function detectLinuxBinaryPath(): string
    {
        foreach (['chromium', 'google-chrome'] as $bin) {
            $path = exec("which $bin");

            if (!empty($path)) {
                return $path;
            }
        }

        return '/usr/bin/google-chrome';
    }

    private function detectWindowsBinaryPath(): string
    {
        $candidates = [
            getenv('ProgramFiles') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe',
            getenv('ProgramFiles(x86)') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe',
            getenv('LOCALAPPDATA') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe'
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $candidate;
    }

    /**
     * @param mixed $binary
     */
    private function checkBinary($binary): string
    {
        // Replace escaped spaces with spaces to check the binary.
        if (!(is_string($binary) && is_executable(str_replace('\ ', ' ', $binary)))) {
            throw new RuntimeException(
                "Invalid Chrome binary: not executable or not existing.\n" .
                "Checked paths: " . implode(', ', $this->getBinaryCandidateList()) . "\n",
                self::ERR_INVALID_BINARY
            );
        }

        return $binary;
    }

    /**
     * @return string[]
     */
    private function getBinaryCandidateList(): array
    {
        switch ($this->platform) {
            case 'linux64':
                return ['chromium', 'google-chrome'];
            case 'mac-x64':
            case 'mac-arm64':
                return ['/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'];
            case 'win32':
            case 'win64':
                return [
                    getenv('ProgramFiles') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe',
                    getenv('ProgramFiles(x86)') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe',
                    getenv('LOCALAPPDATA') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe'
                ];
        }
    }

    /**
     * @throws RuntimeException
     */
    private function detectVersion(): string
    {
        switch ($this->platform) {
            case 'linux64':
            case 'mac-x64':
            case 'mac-arm64':
                $process = new Process([$this->binary, ' --version']);
                break;
            case 'win32':
            case 'win64':
                $process = Process::fromShellCommandline(
                    'reg query "HKEY_CURRENT_USER\Software\Google\Chrome\BLBeacon" /v version'
                );
                break;
        }

        $process->run();
        $chromeVersion = $process->getOutput();

        if ($chromeVersion === '') {
            throw new RuntimeException(
                "Could not detect Chrome version from $this->binary",
                self::ERR_VERSION_NOT_STRING
            );
        }

        $matches = [];
        if (!(
            preg_match('/\s*\d+\.\d+\.\d+\.\d+\s*/', $chromeVersion, $matches)
            && isset($matches[0]) && is_string($matches[0])
        )) {
            throw new RuntimeException(
                "Could not detect Chrome version from $this->binary",
                self::ERR_INVALID_VERSION_FORMAT
            );
        }

        return trim($matches[0]);
    }

    /**
     * @param mixed $version
     */
    private function checkVersion($version): string
    {
        $matches = [];
        if (!(is_string($version) && preg_match('/^.*?(?<major>\d+)(\.\d+\.\d+\.\d+)*$/', $version, $matches))) {
            throw new RuntimeException(
                "Invalid Chrome version: must be in the form X.Y.Z.W.",
                self::ERR_INVALID_VERSION_FORMAT
            );
        }

        return $matches['major'];
    }

    /**
>>>>>>> Stashed changes
     * @throws JsonException
     */
    public function install(string $dir = null): string
    {
        if ($dir === null) {
            global $_composer_bin_dir;
            $dir = $_composer_bin_dir;
            $composerEnvBinDir = getenv('COMPOSER_BIN_DIR');
            if ($composerEnvBinDir && is_string($composerEnvBinDir) && is_dir($composerEnvBinDir)) {
                $dir = $composerEnvBinDir;
            }
        }

        if (!is_dir($dir)) {
            throw new InvalidArgumentException(
                "The directory $dir does not exist.",
                self::ERR_DESTINATION_NOT_DIR
            );
        }

        $this->output->writeln("Fetching Chromedriver version URL ...");

        $zipFilePathname = $this->useEnvZipFile ?
            Env::get('WPBROWSER_CHROMEDRIVER_ZIP_FILE', null)
            : null;
        $cacheDir = FS::cacheDir() . '/chromedriver';
        $executableFileName = $dir . '/' . $this->getExecutableFileName();

        if (!(is_string($zipFilePathname) && is_file($zipFilePathname))) {
            $downloadUrl = $this->fetchChromedriverVersionUrl();
            if (!is_dir($cacheDir) && !(mkdir($cacheDir, 0777, true) && is_dir($cacheDir))) {
                throw new RuntimeException("Could not create Chromedriver cache directory $cacheDir.");
            }
            $zipFilePathname = rtrim($cacheDir, '\\/') . '/chromedriver.zip';
            if (is_file($zipFilePathname) && !unlink($zipFilePathname)) {
                throw new RuntimeException(
                    "Could not remove existing zip file $zipFilePathname",
                    self::ERR_REMOVE_EXISTING_ZIP_FILE
                );
            }
            $this->output->writeln('Downloading Chromedriver to ' . $zipFilePathname . ' ...');
            $zipFilePathname = Download::fileFromUrl($downloadUrl, $zipFilePathname);
            $this->output->writeln('Downloaded Chromedriver to ' . $zipFilePathname);
        }

        if (is_file($executableFileName) && !unlink($executableFileName)) {
            throw new RuntimeException(
                "Could not remove existing executable file $executableFileName",
                self::ERR_REMOVE_EXISTING_BINARY
            );
        }

        Zip::extractFile($zipFilePathname, $this->getExecutableFileName(), $executableFileName);

        if (!chmod($executableFileName, 0755)) {
            throw new RuntimeException(
                "Could not make Chromedriver executable",
                self::ERR_BINARY_CHMOD
            );
        }

        $this->output->writeln("Installed Chromedriver to $executableFileName");

        return $executableFileName;
    }

    /**
     * @throws RuntimeException
     */
    private function detectVersion(): string
    {
        switch ($this->platform) {
            case 'linux64':
            case 'mac-x64':
            case 'mac-arm64':
<<<<<<< Updated upstream
                $process = new Process([$this->binary, ' --version']);
                break;
            case 'win32':
            case 'win64':
                $process = Process::fromShellCommandline(
                    'reg query "HKEY_CURRENT_USER\Software\Google\Chrome\BLBeacon" /v version'
                );
                break;
        }

        $process->run();
        $chromeVersion = $process->getOutput();

        if ($chromeVersion === '') {
            throw new RuntimeException(
                "Could not detect Chrome version from $this->binary",
                self::ERR_VERSION_NOT_STRING
            );
        }

        $matches = [];
        if (!(
            preg_match('/\s*\d+\.\d+\.\d+\.\d+\s*/', $chromeVersion, $matches)
            && isset($matches[0]) && is_string($matches[0])
        )) {
            throw new RuntimeException(
                "Could not detect Chrome version from $this->binary",
                self::ERR_INVALID_VERSION_FORMAT
            );
        }

        return trim($matches[0]);
    }

    /**
     * @throws RuntimeException
     */
    private function detectPlatform(): string
    {
        // Return one of `linux64`, `mac-arm64`,`mac-x64`, `win32`, `win64`.
        $system = php_uname('s');
        $arch = php_uname('m');

        if ($system === 'Darwin') {
            if ($arch === 'arm64') {
                return 'mac-arm64';
            }

            return 'mac-x64';
        }

        if ($system === 'Linux') {
            return 'linux64';
        }

        if ($system === 'Windows NT') {
            if (strpos($arch, '64') !== false) {
                return 'win64';
            }

            return 'win32';
        }

        throw new RuntimeException('Failed to detect platform.', self::ERR_DETECT_PLATFORM);
    }

    /**
     * @return 'linux64'|'mac-x64'|'mac-arm64'|'win32'|'win64'
     *
     * @throws RuntimeException
     * @param mixed $platform
     */
    private function checkPlatform($platform): string
    {
        if (!(is_string($platform) && in_array($platform, [
                'linux64',
                'mac-arm64',
                'mac-x64',
                'win32',
                'win64'
            ]))) {
            throw new RuntimeException(
                'Invalid platform, supported platforms are: linux64, mac-arm64, mac-x64, win32, win64.',
                self::ERR_UNSUPPORTED_PLATFORM
            );
        }

        /** @var 'linux64'|'mac-x64'|'mac-arm64'|'win32'|'win64' $platform */
        return $platform;
    }

    private function detectWindowsBinaryPath(): string
    {
        $candidates = [
            getenv('ProgramFiles') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe',
            getenv('ProgramFiles(x86)') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe',
            getenv('LOCALAPPDATA') . '\\\\Google\\\\Chrome\\\\Application\\\\chrome.exe'
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return $candidate;
    }

    /**
     * @throws RuntimeException
     */
    private function detectBinary(): string
    {
        switch ($this->platform) {
            case 'linux64':
                return '/usr/bin/google-chrome';
            case 'mac-x64':
            case 'mac-arm64':
                return '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome';
            case 'win32':
            case 'win64':
                return $this->detectWindowsBinaryPath();
        }
    }

    /**
     * @param mixed $binary
     */
    private function checkBinary($binary): string
    {
        // Replace escaped spaces with spaces to check the binary.
        if (!(is_string($binary) && is_executable(str_replace('\ ', ' ', $binary)))) {
            throw new RuntimeException(
                "Invalid Chrome binary: not executable or not existing.",
                self::ERR_INVALID_BINARY
            );
        }

        return $binary;
    }

    /**
     * @param mixed $version
     */
    private function checkVersion($version): string
    {
        $matches = [];
        if (!(is_string($version) && preg_match('/^.*?(?<major>\d+)(\.\d+\.\d+\.\d+)*$/', $version, $matches))) {
            throw new RuntimeException(
                "Invalid Chrome version: must be in the form X.Y.Z.W.",
                self::ERR_INVALID_VERSION_FORMAT
            );
        }

        return $matches['major'];
=======
                return 'chromedriver';
            case 'win32':
            case 'win64':
                return 'chromedriver.exe';
        }
>>>>>>> Stashed changes
    }

    private function fetchChromedriverVersionUrl(): string
    {
        return useMemoString(
            function () {
                return $this->unmemoizedFetchChromedriverVersionUrl();
            },
            [$this->platform, $this->milestone]
        );
    }

    /**
     * @throws JsonException
     */
    private function unmemoizedFetchChromedriverVersionUrl(): string
    {
        $milestoneDownloads = file_get_contents(
            'https://googlechromelabs.github.io/chrome-for-testing/latest-versions-per-milestone-with-downloads.json'
        );

        if ($milestoneDownloads === false) {
            throw new RuntimeException(
                'Failed to fetch known good Chrome and Chromedriver versions with downloads.',
                self::ERR_FETCH_MILESTONE_DOWNLOADS
            );
        }

        $decoded = json_decode($milestoneDownloads, true, 512, 0);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }

        if (!(
            is_array($decoded)
            && isset($decoded['milestones'])
            && is_array($decoded['milestones'])
            && isset($decoded['milestones'][$this->milestone])
            && is_array($decoded['milestones'][$this->milestone])
            && isset($decoded['milestones'][$this->milestone]['downloads'])
            && is_array($decoded['milestones'][$this->milestone]['downloads'])
            && isset($decoded['milestones'][$this->milestone]['downloads']['chromedriver'])
            && is_array($decoded['milestones'][$this->milestone]['downloads']['chromedriver'])
        )) {
            throw new RuntimeException(
                'Failed to decode known good Chrome and Chromedriver versions with downloads.',
                self::ERR_DECODE_MILESTONE_DOWNLOADS
            );
        }

        foreach ($decoded['milestones'][$this->milestone]['downloads']['chromedriver'] as $download) {
            if (!(
                is_array($download)
                && isset($download['platform'], $download['url'])
                && is_string($download['platform'])
                && is_string($download['url'])
                && $download['platform'] === $this->platform
            )) {
                continue;
            }

            return $download['url'];
        }

        throw new RuntimeException(
            'Failed to find a download URL for Chromedriver version ' . $this->milestone,
            self::ERR_DOWNLOAD_URL_NOT_FOUND
        );
    }

    private function getExecutableFileName(): string
    {
        switch ($this->platform) {
            case 'linux64':
            case 'mac-x64':
            case 'mac-arm64':
                return 'chromedriver';
            case 'win32':
            case 'win64':
                return 'chromedriver.exe';
        }
    }

    public function getVersion(): string
    {
        return $this->milestone;
    }

    public function getBinary(): string
    {
        return $this->binary;
    }

    public function getPlatform(): string
    {
        return $this->platform;
    }

    public function useEnvZipFile(bool $useEnvZipFile): void
    {
        $this->useEnvZipFile = $useEnvZipFile;
    }
}
