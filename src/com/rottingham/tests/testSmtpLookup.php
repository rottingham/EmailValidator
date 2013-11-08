<?

require __DIR__ . '/../SmtpLookup/SmtpLookup.class.php';
use EmailValidator\SmtpLookup as SmtpLookup;

$email = 'brickleyralph@gmail.com';

$smtp = new SmtpLookup\SmtpLookup();

var_dump($smtp->lookup($email));
