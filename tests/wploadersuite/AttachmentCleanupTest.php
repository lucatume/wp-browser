<?php

use lucatume\WPBrowser\TestCase\WPTestCase;

class AttachmentCleanupTest extends WPTestCase
{
    private static ?int $standaloneAttachment = null;
    private static ?string $standaloneAttachmentFile = null;

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
