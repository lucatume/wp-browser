<?php

/**
 * Class AdminAccessCest
 */
class AdminAccessCest
{
	/**
	 * @test
	 * it should be able to visit the admin area when unlogged
	 */
	public function it_should_be_able_to_visit_the_admin_area_when_unlogged(WpmoduleTester $I)
	{
		$I->amOnAdminPage('/');
		$I->seeElement('body.login');
	}

  /**
   * @test
   * it should be able to log in and access the admin area
   */
  public function it_should_be_able_to_log_in_and_access_the_admin_area(WpmoduleTester $I)
  {
    $I->loginAsAdmin();
    $I->amOnAdminPage('/');
    $I->seeElement('body.index-php.wp-admin');
  }
}
