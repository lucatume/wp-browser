<?php

namespace lucatume\WPBrowser\WordPress\InstallationState;

use Closure;
use lucatume\WPBrowser\Exceptions\InvalidArgumentException;
use lucatume\WPBrowser\Exceptions\RuntimeException;
use lucatume\WPBrowser\Process\Loop;
use lucatume\WPBrowser\Process\ProcessException;
use lucatume\WPBrowser\Process\WorkerException;
use lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory;
use lucatume\WPBrowser\WordPress\DbException;
use lucatume\WPBrowser\WordPress\InstallationException;
use lucatume\WPBrowser\Opis\Closure\ReflectionClosure;
use Throwable;

trait InstalledTrait
{
    /**
     * @var \lucatume\WPBrowser\WordPress\CodeExecution\CodeExecutionFactory
     */
    protected $codeExecutionFactory;

    /**
     * @param mixed $value
     */
    public function updateOption(string $option, $value): int
    {
        $db = $this->getDb();
        $options = $this->db->getTablePrefix() . 'options';

        return $db->query(
            "INSERT INTO $options 
            (option_name, option_value) VALUES (:name, :value)
            ON DUPLICATE KEY UPDATE option_value = :value
            ",
            [
                'value' => $value,
                'name' => $option,
            ]
        );
    }

    /**
     * @throws DbException
     * @throws InstallationException
     */
    private function getBlogName(): string
    {
        $title = $this->db->getOption('blogname');

        if (!is_string($title)) {
            throw new InstallationException(
                "Could not read blogname option from database.",
                InstallationException::INVALID_URL
            );
        }
        return $title;
    }

    /**
     * @throws DbException
     * @throws InstallationException
     */
    private function getBlogDomain(): string
    {
        $siteurl = $this->getBlogSiteurl();

        $domain = parse_url($siteurl, PHP_URL_HOST);

        if (!is_string($domain)) {
            throw new InstallationException(
                "Could not parse domain from siteurl option.",
                InstallationException::INVALID_URL
            );
        }

        return $domain;
    }

    /**
     * @throws DbException
     * @throws InstallationException
     */
    private function getBlogSiteurl(): string
    {
        $siteurl = $this->db->getOption('siteurl');

        if (!is_string($siteurl)) {
            throw new InstallationException(
                "Could not read siteurl option from database.",
                InstallationException::INVALID_URL
            );
        }
        return $siteurl;
    }

    /**
     * @throws WorkerException
     * @throws Throwable
     * @throws ProcessException
     * @return mixed
     */
    public function executeClosureInWordPress(Closure $closure)
    {
        $reflectionClosure = new ReflectionClosure($closure);

        if (!$reflectionClosure->isStatic()) {
            throw new InvalidArgumentException(
                'The closure passed to executeClosureInWordPress must be static.'
            );
        }

        // Unbind the Closure from the current scope to avoid non-autoloader classes causing issues during unserialize.
        $unboundClosure = $closure->bindTo(null, null);

        if (!$unboundClosure instanceof Closure) {
            throw new RuntimeException('Could not unbind closure.');
        }

        $wrappedClosure = $this->codeExecutionFactory->wrapClosureToExecuteInWordPress($unboundClosure);

        return Loop::executeClosure($wrappedClosure, 30, ['rethrow' => true])->getReturnValue();
    }
}
