<?php

use Codeception\Module\WPDb;

class WPDbDbPreparationCest
{
    /**
     * It should remove the admin email verification by default
     *
     * @test
     */
    public function should_remove_the_admin_email_verification_by_default(AcceptanceTester $I)
    {
        $I->seeOptionInDatabase([
            'option_name'  => 'admin_email_lifespan',
            'option_value' => WPDb::ADMIN_EMAIL_LIFESPAN,
        ]);
    }

    /**
     * It should not remove the admin email verification if WPDb.letAdminEmailVerification is true
     *
     * @test
     */
    public function should_not_remove_the_admin_email_verification_if_wp_db_let_admin_email_verification_is_true(AcceptanceTester $I)
    {
        $I->reconfigureWPDb(['letAdminEmailVerification' => true]);

        $I->dontSeeOptionInDatabase([
            'option_name'  => 'admin_email_lifespan',
            'option_value' => WPDb::ADMIN_EMAIL_LIFESPAN
        ]);
    }
}
