<?php


class WPDbAttachmentCest {

	protected $dirs = [];

	public function _before(FunctionalTester $I) {
		$this->removeDirs();
	}

	public function _after(FunctionalTester $I) {
		$this->removeDirs();
	}

	protected function removeDirs() {
		foreach ($this->dirs as $dir) {
			if (is_dir($dir)) {
				rrmdir($dir);
			}
		}
	}

	/**
	 * It should allow having an attachment in the database
	 *
	 * @test
	 */
	public function should_allow_having_an_attachment_in_the_database(FunctionalTester $I) {
		$file = codecept_data_dir('attachments/kitten.jpeg');
		$id   = $I->haveAttachmentInDatabase($file);

		$year  = date('Y');
		$month = date('m');

		$criteria = [
			'post_type'      => 'attachment',
			'post_title'     => 'kitten',
			'post_status'    => 'inherit',
			'post_name'      => 'kitten',
			'post_parent'    => '0',
			'guid'           => $I->grabSiteUrl("/wp-content/uploads/{$year}/{$month}/kitten.jpeg"),
			'post_mime_type' => 'image/jpeg',
		];

		foreach ($criteria as $key => $value) {
			$I->seePostInDatabase(['ID' => $id, $key => $value]);
		}

		$I->seeUploadedFileFound('kitten.jpeg', 'now');
		$I->seeUploadedFileFound('kitten-150x150.jpeg', 'now');
		$I->seeUploadedFileFound('kitten-300x200.jpeg', 'now');
		$I->seeUploadedFileFound('kitten-768x512.jpeg', 'now');

		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attached_file', 'meta_value' => "{$year}/{$month}/kitten.jpeg"]);
		$metadata = [
			'width'      => 1000,
			'height'     => 667,
			'file'       => "{$year}/{$month}/kitten.jpeg",
			'sizes'      => [
				'thumbnail' => [
					'file'      => 'kitten-150x150.jpeg',
					'width'     => 150,
					'height'    => 150,
					'mime-type' => 'image/jpeg',
				],
				'medium'    => [
					'file'      => 'kitten-300x200.jpeg',
					'width'     => 300,
					'height'    => 200,
					'mime-type' => 'image/jpeg',
				],
				'large'     => [
					'file'      => 'kitten-768x512.jpeg',
					'width'     => 768,
					'height'    => 512,
					'mime-type' => 'image/jpeg',
				],
			],
			'image_meta' =>
				[
					'aperture'          => '0',
					'credit'            => '',
					'camera'            => '',
					'caption'           => '',
					'created_timestamp' => '0',
					'copyright'         => '',
					'focal_length'      => '0',
					'iso'               => '0',
					'shutter_speed'     => '0',
					'title'             => '',
					'orientation'       => '0',
					'keywords'          => [],
				],
		];
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attachment_metadata', 'meta_value' => serialize($metadata)]);
	}

	/**
	 * It should allow overriding an attachment date
	 *
	 * @test
	 */
	public function should_allow_overriding_an_attachment_date(FunctionalTester $I) {
		$file = codecept_data_dir('attachments/kitten.jpeg');
		$date = '2016-01-01';
		$id   = $I->haveAttachmentInDatabase($file, $date);

		$criteria = [
			'post_type'      => 'attachment',
			'post_title'     => 'kitten',
			'post_status'    => 'inherit',
			'post_name'      => 'kitten',
			'post_parent'    => '0',
			'guid'           => $I->grabSiteUrl("/wp-content/uploads/2016/01/kitten.jpeg"),
			'post_mime_type' => 'image/jpeg',
		];

		foreach ($criteria as $key => $value) {
			$I->seePostInDatabase(['ID' => $id, $key => $value]);
		}

		$I->seeUploadedFileFound('kitten.jpeg', $date);
		$I->seeUploadedFileFound('kitten-150x150.jpeg', $date);
		$I->seeUploadedFileFound('kitten-300x200.jpeg', $date);
		$I->seeUploadedFileFound('kitten-768x512.jpeg', $date);

		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attached_file', 'meta_value' => "2016/01/kitten.jpeg"]);
		$metadata = [
			'width'      => 1000,
			'height'     => 667,
			'file'       => "2016/01/kitten.jpeg",
			'sizes'      => [
				'thumbnail' => [
					'file'      => 'kitten-150x150.jpeg',
					'width'     => 150,
					'height'    => 150,
					'mime-type' => 'image/jpeg',
				],
				'medium'    => [
					'file'      => 'kitten-300x200.jpeg',
					'width'     => 300,
					'height'    => 200,
					'mime-type' => 'image/jpeg',
				],
				'large'     => [
					'file'      => 'kitten-768x512.jpeg',
					'width'     => 768,
					'height'    => 512,
					'mime-type' => 'image/jpeg',
				],
			],
			'image_meta' =>
				[
					'aperture'          => '0',
					'credit'            => '',
					'camera'            => '',
					'caption'           => '',
					'created_timestamp' => '0',
					'copyright'         => '',
					'focal_length'      => '0',
					'iso'               => '0',
					'shutter_speed'     => '0',
					'title'             => '',
					'orientation'       => '0',
					'keywords'          => [],
				],
		];
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attachment_metadata', 'meta_value' => serialize($metadata)]);
	}

	/**
	 * It should allow overriding each attachment post field
	 *
	 * @test
	 */
	public function should_allow_overriding_each_attachment_post_field(FunctionalTester $I) {
		$file      = codecept_data_dir('attachments/kitten.jpeg');
		$date      = '2016-01-01';
		$overrides = [
			'post_type'      => 'not-an-attachment',
			'post_title'     => 'not-kitten',
			'post_status'    => 'draft',
			'post_name'      => 'not-a-kitten',
			'post_parent'    => '3',
			'guid'           => $I->grabSiteUrl("/wp-content/uploads/2024/05/kitten.jpeg"),
			'post_mime_type' => 'image/png',
		];
		$id        = $I->haveAttachmentInDatabase($file, $date, $overrides);

		foreach ($overrides as $key => $value) {
			$I->seePostInDatabase(['ID' => $id, $key => $value]);
		}

		$I->seeUploadedFileFound('kitten.jpeg', $date);
		$I->seeUploadedFileFound('kitten-150x150.jpeg', $date);
		$I->seeUploadedFileFound('kitten-300x200.jpeg', $date);
		$I->seeUploadedFileFound('kitten-768x512.jpeg', $date);

		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attached_file', 'meta_value' => "2016/01/kitten.jpeg"]);
		$metadata = [
			'width'      => 1000,
			'height'     => 667,
			'file'       => "2016/01/kitten.jpeg",
			'sizes'      => [
				'thumbnail' => [
					'file'      => 'kitten-150x150.jpeg',
					'width'     => 150,
					'height'    => 150,
					'mime-type' => 'image/png',
				],
				'medium'    => [
					'file'      => 'kitten-300x200.jpeg',
					'width'     => 300,
					'height'    => 200,
					'mime-type' => 'image/png',
				],
				'large'     => [
					'file'      => 'kitten-768x512.jpeg',
					'width'     => 768,
					'height'    => 512,
					'mime-type' => 'image/png',
				],
			],
			'image_meta' =>
				[
					'aperture'          => '0',
					'credit'            => '',
					'camera'            => '',
					'caption'           => '',
					'created_timestamp' => '0',
					'copyright'         => '',
					'focal_length'      => '0',
					'iso'               => '0',
					'shutter_speed'     => '0',
					'title'             => '',
					'orientation'       => '0',
					'keywords'          => [],
				],
		];
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attachment_metadata', 'meta_value' => serialize($metadata)]);
	}

	/**
	 * It should allow definining the image sizes to create
	 *
	 * @test
	 */
	public function should_allow_definining_the_image_sizes_to_create(FunctionalTester $I) {
		$file       = codecept_data_dir('attachments/kitten.jpeg');
		$date       = '2016-01-01';
		$imageSizes = [
			'thumbnail' => [200, 200],
			'normal'    => 500,
			'foo'       => [450, 130],
		];

		$id = $I->haveAttachmentInDatabase($file, $date, [], $imageSizes);

		$I->seeUploadedFileFound('kitten.jpeg', $date);
		$I->seeUploadedFileFound('kitten-200x200.jpeg', $date);
		$I->seeUploadedFileFound('kitten-500x333.jpeg', $date);
		$I->seeUploadedFileFound('kitten-450x130.jpeg', $date);

		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attached_file', 'meta_value' => "2016/01/kitten.jpeg"]);
		$metadata = [
			'width'      => 1000,
			'height'     => 667,
			'file'       => "2016/01/kitten.jpeg",
			'sizes'      => [
				'thumbnail' => [
					'file'      => 'kitten-200x200.jpeg',
					'width'     => 200,
					'height'    => 200,
					'mime-type' => 'image/jpeg',
				],
				'normal'    => [
					'file'      => 'kitten-500x333.jpeg',
					'width'     => 500,
					'height'    => 333,
					'mime-type' => 'image/jpeg',
				],
				'foo'     => [
					'file'      => 'kitten-450x130.jpeg',
					'width'     => 450,
					'height'    => 130,
					'mime-type' => 'image/jpeg',
				],
			],
			'image_meta' =>
				[
					'aperture'          => '0',
					'credit'            => '',
					'camera'            => '',
					'caption'           => '',
					'created_timestamp' => '0',
					'copyright'         => '',
					'focal_length'      => '0',
					'iso'               => '0',
					'shutter_speed'     => '0',
					'title'             => '',
					'orientation'       => '0',
					'keywords'          => [],
				],
		];
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attachment_metadata', 'meta_value' => serialize($metadata)]);
	}

	/**
	 * It should not create any additional image when adding non image attachments
	 *
	 * @test
	 */
	public function should_not_create_any_additional_image_when_adding_non_image_attachments(FunctionalTester $I) {
		$file = codecept_data_dir('attachments/pdf-doc.pdf');

		$id = $I->haveAttachmentInDatabase($file);

		$year  = date('Y');
		$month = date('m');

		$criteria = [
			'post_type'      => 'attachment',
			'post_title'     => 'pdf-doc',
			'post_status'    => 'inherit',
			'post_name'      => 'pdf-doc',
			'post_parent'    => '0',
			'guid'           => $I->grabSiteUrl("/wp-content/uploads/{$year}/{$month}/pdf-doc.pdf"),
			'post_mime_type' => 'application/pdf',
		];

		foreach ($criteria as $key => $value) {
			$I->seePostInDatabase(['ID' => $id, $key => $value]);
		}

		$I->seeUploadedFileFound('pdf-doc.pdf', 'now');
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_wp_attached_file', 'meta_value' => "{$year}/${month}/pdf-doc.pdf"]);
	}
}
