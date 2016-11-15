<?php
use tad\WPBrowser\Generators\Date;
use tad\WPBrowser\Generators\Links;

class WPDbLinksCest
{

	public function _before(FunctionalTester $I)
	{
	}

	public function _after(FunctionalTester $I)
	{
	}

	/**
	 * @test
	 * it should allow having a link in the database
	 */
	public function it_should_allow_having_a_link_in_the_database(FunctionalTester $I)
	{
		$now = time();
		Date::_injectNow($now);

		$linkId = $I->haveLinkInDatabase();

		$table = $I->grabLinksTableName();
		$criteria = Links::getDefaults();
		$criteria['link_id'] = $linkId;
		$I->seeInDatabase($table, $criteria);
	}

	/**
	 * @test
	 * it should allow overriding default values
	 */
	public function it_should_allow_overriding_default_values(FunctionalTester $I)
	{

		$overrides = [
			'link_url' => 'http://example.com',
			'link_name' => 'Example',
			'link_image' => 'http://example.com/images/one.jpg',
			'link_target' => '_blank',
			'link_description' => 'An example link',
			'link_visible' => 'N',
			'link_owner' => 12,
			'link_rating' => 14,
			'link_updated' => Date::fromString('today'),
			'link_rel' => 'nofollow',
			'link_notes' => 'Not a real image',
			'link_rss' => 'http://example.com/rss',
		];
		$linkId = $I->haveLinkInDatabase($overrides);

		$table = $I->grabLinksTableName();
		foreach ($overrides as $key => $value) {
			$I->seeInDatabase($table, ['link_id' => $linkId, $key => $value]);
		}
	}

	/**
	 * @test
	 * it should allow having many links in database
	 */
	public function it_should_allow_having_many_links_in_database(FunctionalTester $I)
	{
		$ids = $I->haveManyLinksInDatabase(5);
		$table = $I->grabLinksTableName();
		foreach ($ids as $id) {
			$I->assertTrue(is_int($id));
			$I->seeInDatabase($table, ['link_id' => $id]);
		}
	}

	/**
	 * @test
	 * it should allow using number placeholder when inserting many
	 */
	public function it_should_allow_using_number_placeholder_when_inserting_many(FunctionalTester $I)
	{
		$overrides = [
			'link_url' => 'http://example.com/{{n}}',
			'link_name' => 'Example {{n}}',
			'link_image' => 'http://example.com/images/image-{{n}}.jpg',
			'link_target' => '_blank',
			'link_description' => '{{n}} example link',
			'link_visible' => 'N',
			'link_owner' => 12,
			'link_rating' => 14,
			'link_updated' => Date::fromString('today'),
			'link_rel' => 'nofollow',
			'link_notes' => 'Not a real {{n}} image',
			'link_rss' => 'http://example.com/rss/{{n}}',
		];

		$ids = $I->haveManyLinksInDatabase(5, $overrides);

		$table = $I->grabLinksTableName();
		for ($i = 0; $i < count($ids); $i++) {
			$I->assertTrue(is_int($ids[$i]));
			foreach ($overrides as $key => $value) {
				$I->seeInDatabase($table, ['link_id' => $ids[$i], $key => str_replace('{{n}}', $i, $value)]);
			}
		}
	}

	/**
	 * @test
	 * it should allow not having a link in the database
	 */
	public function it_should_allow_not_having_a_link_in_the_database(FunctionalTester $I)
	{
		$id = $I->haveLinkInDatabase();

		$I->seeLinkInDatabase(['link_id' => $id]);

		$I->dontHaveLinkInDatabase(['link_id' => $id]);

		$I->dontSeeLinkInDatabase(['link_id' => $id]);
	}
}
