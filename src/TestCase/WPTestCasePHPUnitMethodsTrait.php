<?php

namespace lucatume\WPBrowser\TestCase;

use PHPUnit\Runner\Version;

if (version_compare(Version::id(), '8.0', '<')) {
    trait WPTestCasePHPUnitMethodsTrait
    {
        public static function setUpBeforeClass() //@phpstan-ignore-line
        {
            parent::setUpBeforeClass();
            self::getCoreTestCase()->set_up_before_class();
        }

        public static function tearDownAfterClass() //@phpstan-ignore-line
        {
            static::tear_down_after_class();  //@phpstan-ignore-line magic __callStatic
            parent::tearDownAfterClass();
        }

        protected function setUp() //@phpstan-ignore-line
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
            $this->recordAttachmentAddedDuringTest();
        }

        protected function tearDown() //@phpstan-ignore-line
        {
            $this->removeAttachmentsAddedDuringTest();
            $this->restoreAdditionalGlobals();
            $this->tear_down(); //@phpstan-ignore-line magic __call
            parent::tearDown();
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

        public static function tearDownAfterClass(): void
        {
            static::tear_down_after_class();  //@phpstan-ignore-line magic __callStatic
            parent::tearDownAfterClass();
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
            $this->recordAttachmentAddedDuringTest();
        }

        protected function tearDown(): void
        {
            $this->removeAttachmentsAddedDuringTest();
            $this->restoreAdditionalGlobals();
            $this->tear_down(); //@phpstan-ignore-line magic __call
            parent::tearDown();
        }
    }
}
