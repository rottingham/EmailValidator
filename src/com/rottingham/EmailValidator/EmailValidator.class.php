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
 * The MIT License (MIT)
 *
 * Copyright (c) 2013 rottingham
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
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
