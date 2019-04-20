## What is a unit test? An acceptance test?
This page has no pretense to be THE source of truth about what is called how in the context of tests; the purpose of this page is to lay out the terminology that I'll use in the documentation to define the levels and components of testing.  
Wikipedia, forums and other documents online will offer alternate, and equally valid, definitions.

## The signup page example
Let's assume I'm testing a WordPress plugin that adds mailing list management and subscription functionalities to a site.  
The plugin provides a number of functions and, among them, it will add a sign-up page to receive users applications.  

### Acceptance tests
In brief: **make assertions as a user would**.  
The user might be tech-savvy as much as I want her to be but still make assertions only on what feedback the site provides. 
The code below tests a user can subscribe to the mailing list:

```php
<?php
// UserSuccessfulSignupTest.php

// Add a page that contains the shortcode that will render the signup form.
$I->havePageInDatabase( [
    'post_name' => 'signup',
    'post_content'=> 'Sign-up for our awesome thing! [signup]',
] );

// Go to the page.
$I->amOnPage( '/signup' );

// Submit the form as a user would submit it. 
$I->submitForm( '#signup-form', [
  'name' => 'Luca',
  'email' => 'luca@theAverageDev.com',
] );

// Make sure I see a confirmation message. 
$I->waitForElement( '#signup-confirmation' );
```

### Functional tests
In brief: **make assertions as a developer would**.  
The test code below asserts front-end submissions are correcty processed from the developer perspective:
```php
<?php
// file tests/functional/SignupSubmissionCest.php

class SignupSubmissionCest {

    public function _before( FunctionalTester $I ) {
        // Add a page that contains the shortcode that will render the signup form.
        $I->havePageInDatabase( [
            'post_name' => 'signup',
            'post_content'=> 'Sign-up for our awesome thing! [signup]',
        ] );

        $I->amOnPage( '/signup' );
    }
    
    public function test_good_signup( FunctionalTester $I ) {
        $I->sendAjaxPostRequest( '/wp-json/acme/v1/signup', [
          '_wpnonce' => $I->grabAttributeFrom( '#signup-nonce', 'value' ),
          'name' => 'Luca',
          'email' => 'luca@theAverageDev.com',
        ] );
        
        $I->seeResponseCodeIsSuccessful();
        $I->seeUserInDatabase( [ 'user_login' => 'luca', 'user_email' => 'luca@theaveragedev.com' ] );
    }
    
    public function test_bad_email_signup( FunctionalTester $I ) {
        $I->sendAjaxPostRequest( '/wp-json/acme/v1/signup', [
          '_wpnonce' => $I->grabAttributeFrom( '#signup-nonce', 'value' ),
          'name' => 'Luca',
          'email' => 'not-really-an-email',
        ] );

        $I->seeResponseCodeIs( 400 );
        $I->dontSeeUserInDatabase( [ 'user_login' => 'luca', 'user_email' => 'not-really-an-email' ] );
    }
}
```
    
The code looks, initially, like an acceptance test, but differs in its action and assertion phase: in place of filling a form and clicking "Submit" it sends a `POST` request to a REST API endpoint and checks the effect of the submission in the database.  
All of these actions fall squarely into what a developer would do, not into what a user could/should be able to do.  
Furthermore, the format of the test is not the same as the one used in the acceptance test.  
The acceptance test is written in the most eloquent testing format supported by Codeception, the [Cept format](https://codeception.com/docs/02-GettingStarted), this test uses a more PHPUnit-like format, the [Cest format](https://codeception.com/docs/07-AdvancedUsage#Cest-Classes).  
While the first is easier to skim for  non-developers the second harnesses the power of a re-using pieces of code, the page creation and navigation in the example, to optimize the test code.

### Integration test
In brief: **test code modules in the context of a WordPress website**.
In this type of test the WordPress, and additional plugins code, is loaded in the same variable scope as the tests; this is why in the example below I'm using classes (`WP_REST_Request`, `WP_REST_Response`) and methods (`register_rest_route`) defined by WordPress, not the plugin code.  
The REST API request sent by the application form will be handled by a class, `Acme\Signup\SubmissionHandler`, that's been attached to the `/wp-json/acme/v1/signup` path:

```php
<?php
// file src/rest.php

add_action( 'rest_api_init', function () {
	register_rest_route( 'acme/v1', '/signup', array(
		'methods' => 'POST',
		'callback' => function( WP_Rest_Request $request ) {
		    $email_validator = new Acme\Signup\EmailValidator();
		    $handler = new Acme\Signup\SubmissionHandler( $email_validator );
		    
		    return $handler->handle( $request );
		},
	) );
} );
```

I want to test the chain of classes and methods that's handling such a request in the context of a WordPress installation.  
Integration is usually about testing "modules" of code: groups of classes and functions working together to provide a service or complete a task.  
In the context of integration testing the class dependencies and/or the context are **not** mocked.

```php
<?php
// file tests/integration/SubmissionHandlingTest.php

class SubmissionHandlingTest extends \Codeception\TestCase\WPTestCase {
    public function test_good_request() {
        $request = new WP_Rest_Request();
        $request->set_body_params( [ 'name' => 'luca', 'email' => 'luca@theaveragedev.com' ] );
        $handler = new  Acme\Signup\SubmissionHandler();
        
        $response = $handler->handle( $request );
        
        $this->assertIntsanceOf( WP_REST_Response::class, $response );
        $this->assertEquals( 200, $response->get_status() );
        $this->assertInstanceOf( Acme\Signup\Submission_Good::class, $handler->last_submission() );
        $this->assertEquals( 'luca', $handler->last_submission()->name() );
        $this->assertEquals( 'luca@theaveragedev.com', $handler->last_submission()->email() );
    }
    
    public function test_bad_email_request() {
        $request = new WP_Rest_Request();
        $request->set_body_params( [ 'name' => 'luca', 'email' => 'not-a-valid-email' ] );
        $handler = new  Acme\Signup\SubmissionHandler();
        
        $response = $handler->handle( $request );
        
        $this->assertIntsanceOf( WP_REST_Response::class, $response );
        $this->assertEquals( 400, $response->get_status() );
        $this->assertInstanceOf( Acme\Signup\Submission_Bad::class, $handler->last_submission() );
        $this->assertEquals( 'luca', $handler->last_submission()->name() );
        $this->assertEquals( 'not-a-valid-email', $handler->last_submission()->email() );
    }
}
```

The test format used is the familiar [PhpUnit](https://phpunit.de/ "PHPUnit – The PHP Testing Framework") one; the only difference is the base test class that's being extended (`\Codeception\TestCase\WPTestCase`) is one provided by wp-browser.  
In the context of WordPress "integration" might also mean testing that filters used by the code have the expected effect.  


### Unit tests
In brief: **test single classes or functions in isolation**.
The email address is validated by the `Acme\Signup\EmailValidator` class.  
In the test code below I want to make sure the validation works as intended.

```php
<?php
// file tests/unit/EmailValidatorTest.php

class EmailValidatorTest extends Codeception\Test\Test {
    public function test_good_email_validation() {
        $validator = new Acme\Signup\EmailValidator();
        
        $this->assertTrue( $validator->validate( 'luca@theaveragedev.com' ) ); 
    }
    
    public function test_bad_email_validation(){
        $validator = new Acme\Signup\EmailValidator();
        
        $this->assertTrue( $validator->validate( 'not-an-email' ) );
    }
    
    public function test_tricky_email_validation() {
        $validator = new Acme\Signup\EmailValidator();
        
        $this->assertTrue( $validator->validate( 'luca+signup@theaveragedev.com' ) ); 
    }
    
    public function test_validation_with_service(){
        // Stub the validation service.
        $validation_service = $this->prophesize( Acme\Signup\ValidationService::class );
        $validation_service->validate( 'luca@theaveragedev.com' )->willReturn( true );
        $validation_service->validate( 'lucas@theaveragedev.com' )->willReturn( false );
        // Build the validator and set it to use the mock validation service.
        $validator = new Acme\Signup\EmailValidator();
        $validator->use_service( $validation_service->reveal() );
        
        $this->assertTrue( $validator->validate( 'luca@theaveragedev.com' ) );
        $this->assertFalse( $validator->validate( 'lucas@theaveragedev.com' ) );
    }
}
```

Unit tests is where stubbing/mocking/spying of dependencies is used to gain total control over the input and context the class is using.  
In the last test method I'm doing exactly that testing the email validator with an external validation service.
In the example I'm using the [Prophecy mock engine](https://github.com/phpspec/prophecy) that comes [with PHPUnit](https://phpunit.de/manual/6.5/en/test-doubles.html) along with its own mocking/stubbing/spying solutions.  
There are other mocking engines (e.g [Mockery](http://docs.mockery.io/en/latest/)) that could be used.

### WordPress "unit" tests
In brief: **test single classes or functions that require WordPress code in as much isolation as possible**.  
This is what most people referring to "unit tests" in the context of WordPress is talking about.  
The purpose of this kind of tests is to test **one** class of a WordPress application, or one function, that **requires a WordPress-defined function or class** with a unit testing approach.  
In the example below I'm testing the `Acme\Signup\SubmissionHandler` class on a "unit" level making sure it will mark a request as bad if the email is not a valid one. 

```php
<?php
// file tests/unit/SubmissionHandlerTest.php
class SubmissionHandlerTest extends Codeception\Test\Test {
    protected  $request;
    protected $validator;
    
    public function setUp() {
        // Mock the request.
        $this->request = $this->prophesize( WP_REST_Request::class );
        // Spy on the validator.
        $this->validator = $this->prophesize( Acme\Signup\EmailValidator::class );
    }
    
    public function test_email_is_validated_by_default() {
        $this->request->get_param( 'name' )->willReturn( 'luca' );
        $this->request->get_param( 'email' )->willReturn( 'luca@theaveragedev.com' );
        
        $handler = new Acme\Signup\SubmissionHandler( $this->validator->reveal() );
        $handler->set_validator( $this->validator );
        $response = $handler->handle( $this->request->reveal() );
        
        $this->assertInstanceOf( WP_REST_Response::class, $response );
        // Verify on the validator spy.
        $this->validator->validate( 'luca@theaveragedev.com' )->shouldHaveBeenCalled();
    }
    
    public function test_will_not_validate_email_if_missing() {
        $this->request->get_param( 'name' )->willReturn( 'luca' );
        $this->request->get_param( 'email' )->willReturn( '' );
        
        $handler = new Acme\Signup\SubmissionHandler( $this->validator->reveal() );
        $handler->set_validator( $this->validator );
        $response = $handler->handle( $this->request->reveal() );
        
        $this->assertInstanceOf( WP_REST_Response::class, $response );
        // Verify on the validator spy.
        $this->validator->validate( Argument::any() )->shouldNotHaveBeenCalled();
    }
}
```

The class uses the `WP_REST_Request` and `WP_Rest_Response` classes as input and output and will probably, internally, use more functions defined by WordPress.  
One solution to avoid loading WordPress, could be to rewrite test versions of each and all the WordPress functions and classes needed by all the classes I want to unit test; this would require updating each time the classes requirements change.  
Furthermore i18n (e.g. `__()`) and filtering (e.g `apply_filters`) functions would not need to be mocked if not in specific cases and would pretty much be copy and paste versions of the WordPres ones.  
Loading single pieces of WordPress is a dangerous and brittle endeavour and it's not supported by the framework.
To avoid all this WordPress "unit tests" pay the price of having to bootstrap WordPress, thus requiring a database connection.
This kind of test setup and level is the one you can see in the [PHPUnit Core suite of WordPress itself](https://make.wordpress.org/core/handbook/testing/automated-testing/phpunit/).

