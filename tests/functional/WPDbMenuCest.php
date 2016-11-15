<?php


class WPDbMenuCest
{

	public function _before(FunctionalTester $I)
	{
	}

	public function _after(FunctionalTester $I)
	{
	}


	/**
	 * @test
	 * it should throw if trying to add menu when theme not set
	 */
	public function it_should_throw_if_trying_to_add_menu_when_theme_not_set(FunctionalTester $I)
	{
		try {
			$ids = $I->haveMenuInDatabase('menu1', 'header');
		} catch (\RuntimeException $e) {
			return;
		}
		$I->fail('Should throw');
	}

	/**
	 * @test
	 * it should throw if trying to add menu items when theme not set
	 */
	public function it_should_throw_if_trying_to_add_menu_items_when_theme_not_set(FunctionalTester $I)
	{
		try {
			$I->haveMenuItemInDatabase('menu1', 'Link');
		} catch (\RuntimeException $e) {
			return;
		}
		$I->fail('Should throw');
	}

	/**
	 * @test
	 * it should add a menu in the databaase
	 */
	public function it_should_add_a_menu_in_the_databaase(FunctionalTester $I)
	{
		$I->useTheme('foo');

		$ids = $I->haveMenuInDatabase('menu1', 'header');

		$I->seeTermInDatabase(['name' => 'menu1', 'slug' => 'menu1']);
		$I->seeOptionInDatabase([
			'option_name' => 'theme_mods_foo',
			'option_value' => ['nav_menu_locations' => ['header' => reset($ids)]]
		]);
	}

	/**
	 * @test
	 * it should allow adding menus to different themes
	 */
	public function it_should_allow_adding_menus_to_different_themes(FunctionalTester $I)
	{
		$I->useTheme('foo3');

		$ids = $I->haveMenuInDatabase('menu1', 'header');

		$I->seeTermInDatabase(['name' => 'menu1', 'slug' => 'menu1']);
		$I->seeOptionInDatabase([
			'option_name' => 'theme_mods_foo3',
			'option_value' => ['nav_menu_locations' => ['header' => reset($ids)]]
		]);

		$I->useTheme('bar3');

		$ids = $I->haveMenuInDatabase('menu1', 'header');

		$I->seeTermInDatabase(['name' => 'menu1', 'slug' => 'menu1']);
		$I->seeOptionInDatabase([
			'option_name' => 'theme_mods_bar3',
			'option_value' => ['nav_menu_locations' => ['header' => reset($ids)]]
		]);

		$I->useTheme('foo3');

		$ids = $I->haveMenuInDatabase('menu2', 'footer');

		$I->seeTermInDatabase(['name' => 'menu2', 'slug' => 'menu2']);
		$I->seeOptionInDatabase([
			'option_name' => 'theme_mods_foo3',
			'option_value' => ['nav_menu_locations' => ['footer' => reset($ids)]]
		]);
	}

	/**
	 * @test
	 * it should throw if trying to add items to non existing menu
	 */
	public function it_should_throw_if_trying_to_add_items_to_non_existing_menu(FunctionalTester $I)
	{
		try {
			$I->useTheme('foo');
			$I->haveMenuItemInDatabase('menuFoo', 'Link');
		} catch (\RuntimeException $e) {
			return;
		}
		$I->fail('Should throw');
	}

	/**
	 * @test
	 * it should allow adding menu items to menus
	 */
	public function it_should_allow_adding_menu_items_to_menus(FunctionalTester $I)
	{
		$I->useTheme('foo2');
		$menuIds = $I->haveMenuInDatabase('menu1', 'header');

		$id = $I->haveMenuItemInDatabase('menu1', 'Link1');

		$I->seePostInDatabase(['ID' => $id, 'post_type' => 'nav_menu_item', 'menu_order' => 1]);
		$I->seeTermRelationshipInDatabase(['object_id' => $id, 'term_taxonomy_id' => $menuIds[1]]);
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_menu_item_object', 'meta_value' => 'custom']);
		$I->seePostMetaInDatabase(['post_id' => $id, 'meta_key' => '_menu_item_type', 'meta_value' => 'custom']);
		$I->seePostMetaInDatabase([
			'post_id' => $id,
			'meta_key' => '_menu_item_url',
			'meta_value' => 'http://example.com'
		]);
	}
}
