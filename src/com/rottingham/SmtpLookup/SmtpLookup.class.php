<?

namespace EmailValidator\SmtpLookup;

/**
 * SMTP Lookup Utility
 *
 *
 * Author : Ralph Brickley
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
class SmtpLookup {

	/**
	 * Configuration options
	 * @var array
	 */
	protected $config;

	/**
	 * Socket
	 * @var resource
	 */
	protected $socket;

	/**
	 * Constructor
	 *
	 * Defines default configuration options
	 */
	public function __construct(array $config = null) {
		$this->config = array (
			'debug' => false,								// Enable Debug Message Out
			'port' => 25,									// Port to send requests from
			'maxConnTime' => 3,								// Max Time in Seconds to wait for Connect
			'maxReadTime' => 5,								// Max Time in Seconds to wait for Responses
			'hostName' => 'localhost',						// Host name
			'nameServers' => array (						// Name servers to use
				'192.168.0.1'
			),
			'validResponseCodes' => array (					// Valid SMTP Response Codes. Any others will be rejected
				250,										// and the email address lookup will return FALSE
				451,
				452
			),
			'sendFrom' => 'user@localhost'					// Send mail from
		);

		// Merge user supplied config options
		if ($config) {
			$this->config = array_merge($this->config, array_unique($config, $this->config));
		}
	}

	/**
	 * Lookup Email Address
	 *
	 * Looks up the Email address by speaking to the domain and validating
	 * the inbox.
	 *
	 * @param string $email Email to lookup
	 * @throws InvalidArgumentException If the Email address supplied is empty.
	 * @return Returns TRUE if the email is found and a valid response code is
	 * received. FALSE otherwise.
	 */
	public function lookup($email) {
		$email = trim($email);

		if (strlen($email) <= 0) {
			throw new \InvalidArgumentException('Email Address cannot be an empty value. Please provide a valid email address.', 0);
		}

		$found = false;

		// Split our user
		$user = (object)$this->parseEmail($email);

		// retrieve SMTP Server via MX query on domain
		$records = $this->queryMxEntries($user->domain);

		// Get a socket connection
		$socket = $this->getSocket($records);

		// If we received an open socket, ask about the email address
		if ($socket) {
			$this->debug('Socket connection created');

			$reply = fread($socket, 2082);
			$this->debug("<<<\n$reply");
			$code = $this->getResponseCode($reply);

			// If the response code is not 220, we can't talk.
			if ($code != 220) {
				$this->closeSocket($socket);
				return false;
			}

			// Say HELO to the Domain
			$reply = $this->send("HELO " . $this->config['hostName'], $socket);

			// If the response includes '250 localhost'
			// we reached no legit mail server
			if (preg_match('/250 localhost/i', $reply) === 1) {
				$this->closeSocket($socket);
				return false;
			}

			// Tell Domain who we are
			$reply = $this->send("MAIL FROM: <" . $this->config['sendFrom'] . ">", $socket);

			// Tell Domain who we are looking up
			$reply = $this->send("RCPT TO: <" . $user->username . '@' . $user->domain . ">", $socket);

			// Get the response code
			$code = $this->getResponseCode($reply);

			// If the response code is in our valid code array
			if (in_array($code, $this->config['validResponseCodes'])) {
				$found = true;
			}

			$this->closeSocket($socket);
			return $found;

		} else {
			$this->debug('No socket connection created');
			$this->closeSocket($socket);
			return false;
		}
	}


	/**
	 * parseEmail
	 *
	 * Split the email address into user/domain parts.
	 *
	 * @param string $email Email to be broken into parts
	 * @return Returns an array of parsed email parts
	 */
	private function parseEmail($email) {
		$parts = explode('@', $email);
		$domain = array_pop($parts);
		$user = implode('@', $parts);

		return array(
			'username'=>$user,
			'domain'=>$domain
		);
	}

	/**
	 * Query DNS server for MX entries and sorts them in
	 * order or the record weight.
	 *
	 * @param string $domain Domain to query
	 * @return Returns an array of MX host
	 */
	private function queryMxEntries($domain) {
		$hosts = array();
		$hostWeights = array();
		$records = array();

		// Get Mail Exchange Records
		getmxrr($domain, $hosts, $hostWeights);

		// Sort by weighting
		$numHosts = count($hosts);
		for ($i = 0; $i < $numHosts; $i++) {
			$records[$hosts[$i]] = $hostWeights[$i];
		}

		asort($records);

		return $records;
	}

	/**
	 * Set a message
	 * @param string $msg Message to send
	 * @param
	 * @return Returns the reply string from the socket
	 */
	private function send($msg, $socket) {
		fwrite($socket, $msg . "\r\n");
		$reply = fread($socket, 2082);
		$this->debug(">>>\n$msg\n");
		$this->debug("<<<\n$reply\n");

		return $reply;
	}

	/**
	 * getSocket
	 *
	 * Looks through the available MX records/hosts and attempts to create
	 * a connection.
	 *
	 * @param array $records Host Records
	 * @return Returns the FSOCK resource if a connection is made, or FALSE.
	 */
	private function getSocket(array $records) {
		$port = $this->config['port'];
		$timeout = $this->config['maxConnTime'];
		$readTime = $this->config['maxReadTime'];
		$errorNo = 0;
		$errorStr = '';
		$socket = null;

		foreach ($records as $host => $weight) {
			$this->debug("Trying host $host:$port\n");
			$socket = fsockopen($host, $port, $errorNo, $errorStr, $timeout);
			if ($socket) {
				stream_set_timeout($socket, $readTime);
				break;
			}
		}

		return $socket;
	}

	/**
	 * closeSocket
	 *
	 * Closes the socket connection
	 *
	 * @param resource $socket
	 * @return void
	 */
	private function closeSocket($socket) {
		if ($socket) {
			$this->send('Closing socket...', $socket);
			fclose($socket);
		}
	}


	/**
	 * getResponseCode
	 *
	 * Looks for a 3 digit repsonse code in the socket reply
	 *
	 * @param string $reply Reply from the MX Host
	 * @return Returns the code if found, 0 if no response code is found
	 */
	private function getResponseCode($reply) {
		preg_match('/^[0-9]{3}/ims', $reply, $matches);
		$code = (isset($matches[0])) ? $matches[0] : 0;
		return intval($code);
	}



	/**
	 * Debug
	 *
	 * Echos debug messages if the debug config is set to TRUE.
	 *
	 * @param string $str String to be Echo'd
	 * @return void
	 */
	private function debug($str) {
		if ($this->config['debug']) {
			echo "<p>$str</p>";
		}
	}
}
