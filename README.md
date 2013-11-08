Email Validator
============

Utility Class to validate Email address string as well as determine if the Email inbox exists on the mail server by using an SMTP lookup.

For extended usage examples, see test files.

**Composer Reader**

#### Sample Usage

Simply import the `Emailvalidator.class.php` file into your project if you are not using dependency injection.

    require __DIR__ . '/src/com/rottingham/EmailValidator/EmailValidator.class.php';

##### Valiate Email

To validate an Email string, use the `EmailValidator::validate(email)` method;

    $email = 'imlegit@google.com';
    $isValid = EmailValidator\EmailValidator::validate($email);
    var_dump($isValid);
    
#### Check if Email Exists

To determine if the Emai actaully exists on the mail server, use the `EmailValidator::exists(email)` method;

    $email = 'imlegit@yahoo.com';
    $exists = EmailValidator\EmailValidator::exists($email);
    var_dump($exists);
    
**Note:** `EmailValidator::exists(url)` calls `EmailValidator\SmtpLookup\SmtpLookup::lookup(email)` and requests from the mail server whether or not the address book record exists. This could fail if your IP address and/or mail server have been blacklisted.


#### Email Lookup

You can use the `EmailValidator\SmtpLookup.class.php` class seperately by importing it into your project.

    require __DIR__ . '/src/com/rottingham/SmtpLookup/SmtpLookup.class.php';
