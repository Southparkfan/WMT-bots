<?php
class IRCClient {
	protected $sock;

	private $server;
	private $port;
	private $nickname;
	private $password;

	public function __construct($server, $port = 6667, $nickname, $username, $password) {
		if (!$server) {
			die('$server isn\'t set.');
		} elseif (!$nickname) {
			die('$nickname isn\'t set.');
		}

		$this->server = $server;
		$this->port = $port;
		$this->nickname = $nickname;
		$this->username = $username;
		$this->password = $password;

		$sock = $this->connect($server, $port, $nickname, $password);

		if (!$sock) {
			die("Couldn't connect to $server - error: $sock");
		}
	}

	public function connect() {
		$this->sock = fsockopen($this->server, $this->port, $errno, $errstr);

		if (!$this->sock && $errstr) {
			return $errstr;
		}
		
		stream_set_blocking($this->sock, 0);

		$this->sendData("USER $nick $nick $nick :$nick");
		$this->sendData("NICK $nick");

		if ($this->password != '') {
			$this->identify($this->password);
		}
	}
	
	public function sendData($data) {
		fputs($this->sock, $data . "\r\n");
	}

	public function getData() {
		return trim(fgets($this->sock, 1024));
	}

	public function say($channel, $message) {
		$this->sendData("PRIVMSG $channel :$message");
	}

	private function identify($username, $password) {
		$this->sendData("PRIVMSG NickServ : IDENTIFY $username $password");
	}

	public function join($channel) {
		$this->sendData("JOIN $channel");
	}
}
