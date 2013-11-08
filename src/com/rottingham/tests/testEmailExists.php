<?

require __DIR__ . '/../EmailValidator/EmailValidator.class.php';
use EmailValidator\EmailValidator as EmailValidator;

$email = 'happymaddy@gmail.com';
$email2 = 'yoyojo@yahoo.com';

var_dump(EmailValidator::exists($email));
var_dump(EmailValidator::exists($email2));
