## What is a unit test? An acceptance test?
This page has no pretense to be THE source of truth about what is called how in the context of tests.  
I've got little affection for any definition keeping developers away from actually writing tests but, for the sake of clarity, I need to lay out the terminology I will use to define what's shown in the documentation.  

## The signup page example
Let's assume we're testing a WordPress plugin that adds mailing list management functionalities to a site.  
Among the number of functionalities the plugin provides it will add a sign-up page to receive users applications.  

### Acceptance tests
Make assertions as a user would.  
The user might be tech-savvy as much as we want her to be but still make assertions only on what feedback the site provides. 
The code below tests a user can subscribe to the mailing list:

```php
// UserSuccessfulSignupCept.php
<?php
// Add a page that contains the shortcode that will render the signup form.
$I->havePageInDatabase([
    'post_name' => 'signup',
    'post_content'=> 'Sign-up for our awesome thing! [signup]',
]);

// Go to the page.
$I->amOnPage('/signup');

// Submit the form as a user would submit it. 
$I->submitForm('#signup-form', [
  'name' => 'Luca',
  'email' => 'luca@theAverageDev.com'
]);

// Make sure we see a confirmation message. 
$I->waitForElement('#signup-confirmation');
```

### Functional tests
Make assertions as a developer would.  
The test code below asserts front-end submissions are correcty processed from the user perspective:
```php
<?php
// Add a page that contains the shortcode that will render the signup form.
$I->havePageInDatabase([
    'post_name' => 'signup',
    'post_content'=> 'Sign-up for our awesome thing! [signup]',
]);

$I->amOnPage('/signup');

$I->sendAjaxPostRequest('/wp-json/my-api/v1/signup', [
  '_wpnonce' => $I->grabAttributeFrom('#signup-nonce', 'value'),
  'name' => 'Luca',
  'email' => 'luca@theAverageDev.com'
]);

$I->seeUserInDatabase(['user_login' => 'luca', 'user_email' => 'luca@theaveragedev.com']);
```
    
The code looks initially like the `acceptance` one but differs in its action and assertion phase: in place of filling a form and clicking "Submit" it sends a `POST` request to a REST API endpoint and checks the effect of the submission in the database.

### Integration test
The REST API request sent by 

### WordPress Unit test

### Unit test

## Cept, Cest, Test...
