<?

namespace EmailValidator;

require_once __DIR__ . '/../SmtpLookup/SmtpLookup.class.php';
use EmailValidator\SmtpLookup as SmtpLookup;


/**
 * Email Validator
 *
 * Email Validator provides the tools to validate the email string
 * format as well as provide an SMTP MX lookup to validate the email address.
 *
 * Author: Ralph Brickley <brickleyralph@gmail.com>
 *
 *
 */
class EmailValidator {

	/**
	 * Validate Eamil String
	 *
	 * Uses PHP's filter_var method to ensure the Email string is valid.
	 *
	 * @param string $email Email to validate
	 * @throws InvalidArgumentException If the provided Email string is empty.
	 * @return Returns TRUE if the valid string is a legitimate URL
	 */
	public static function validate($email) {
		$email = trim($email);

		if (strlen($email) <= 0) {
			throw new \InvalidArgumentException('Email string cannot be empty.', 0);
		}

		return filter_var($email, FILTER_VALIDATE_EMAIL) === $email;
	}


	/**
	 * Exists
	 *
	 * Uses SMTP/MX Query Requests to check whether or not the Email
	 * address exists on the server.
	 *
	 * **This requires an active, open net connection**
	 * **This could fail if your outbound IP/mail server has been blacklisted**
	 *
	 * @param string $email Email address to discover
	 * @return Returns TRUE if the server response verifies the Email address.
	 * FALSE otherwise.
	 */
	public static function exists($email) {

		if (!EmailValidator::validate($email)) {
			return false;
		}

		$smtp = new SmtpLookup\SmtpLookup();
		return $smtp->lookup($email);
	}

}
