<?php

namespace lucatume\WPBrowser\TestCase;

use PHPUnit\Runner\Version;

if (version_compare(Version::id(), '8.0', '<')) {
    trait WPTestCasePHPUnitMethodsTrait
    {
        public static function setUpBeforeClass()
        {
            parent::setUpBeforeClass();
            self::getCoreTestCase()->set_up_before_class();
        }

        protected function setUp()
        {
            parent::setUp();

            // Restores the uploads directory if removed during tests.
            $uploads = wp_upload_dir();
            if (!is_dir($uploads['basedir'])
                && !mkdir($uploads['basedir'], 0755, true)
                && !is_dir($uploads['basedir'])) {
                throw new \RuntimeException('Failed to create uploads base directory.');
            }

            $this->set_up(); //@phpstan-ignore-line magic __call
            $this->backupAdditionalGlobals();
        }

        protected function tearDown()
        {
            $this->restoreAdditionalGlobals();
            $this->tear_down(); //@phpstan-ignore-line magic __call
            parent::tearDown();
        }


        public static function tearDownAfterClass()
        {
            static::tear_down_after_class();  //@phpstan-ignore-line magic __callStatic
            parent::tearDownAfterClass();
        }
    }
} else {
    trait WPTestCasePHPUnitMethodsTrait
    {
        public static function setUpBeforeClass(): void
        {
            parent::setUpBeforeClass();
            self::getCoreTestCase()->set_up_before_class();
        }

        protected function setUp(): void
        {
            parent::setUp();

            // Restores the uploads directory if removed during tests.
            $uploads = wp_upload_dir();
            if (!is_dir($uploads['basedir'])
                && !mkdir($uploads['basedir'], 0755, true)
                && !is_dir($uploads['basedir'])) {
                throw new \RuntimeException('Failed to create uploads base directory.');
            }

            $this->set_up(); //@phpstan-ignore-line magic __call
            $this->backupAdditionalGlobals();
        }

        protected function tearDown(): void
        {
            $this->restoreAdditionalGlobals();
            $this->tear_down(); //@phpstan-ignore-line magic __call
            parent::tearDown();
        }


        public static function tearDownAfterClass(): void
        {
            static::tear_down_after_class();  //@phpstan-ignore-line magic __callStatic
            parent::tearDownAfterClass();
        }
    }
}
