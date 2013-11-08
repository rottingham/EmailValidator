<?

require __DIR__ . '/../EmailValidator/EmailValidator.class.php';
use EmailValidator\EmailValidator as EmailValidator;

$email = 'happymaddy@gmail.com';
$email2 = 'yoyojo@yahoo.com';
$email3 = ' missingparts.com';

var_dump(EmailValidator::validate($email));
var_dump(EmailValidator::validate($email2));
var_dump(EmailValidator::validate($email3));
