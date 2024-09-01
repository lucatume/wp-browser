<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class AttachmentCleanupTest extends WPTestCase
{
    /**
     * @var int|null
     */
    private static $standaloneAttachment;
    /**
     * @var string|null
     */
    private static $standaloneAttachmentFile;

    public function testCreatesAttachments(): void
    {
        self::$standaloneAttachment = static::factory()->attachment->create_upload_object(
            codecept_data_dir('attachments/kitten.jpeg')
        );

        $this->assertIsInt(self::$standaloneAttachment);
        $this->assertTrue(file_exists(get_attached_file(self::$standaloneAttachment)));
        self::$standaloneAttachmentFile = get_attached_file(self::$standaloneAttachment);
    }

    public function testAttachmentsCreatedWithCreateUploadObjectAreDeleted(): void
    {
        $this->assertEquals('', get_attached_file(self::$standaloneAttachment));
        $this->assertFalse(file_exists(self::$standaloneAttachmentFile));
    }
}
