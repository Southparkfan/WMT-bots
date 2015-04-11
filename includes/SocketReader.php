class SocketReader {
	protected $socket;
	protected $socklocation;
	protected $server;

	public function __construct($socklocation, $server, $message) {
		if (!extension_loaded('socket')) {
			die('The socket extension is not loaded.');
		}

		$this->socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);

		if (!$this->socket) {
			die('Unable to create the AF_UNIX socket');
		}

		if (file_exists($socklocation)) {
			unlink($socklocation);
		}

		if (!socket_bind($this->socket, $socklocation)) {
			die('Unable to bind socket to ' . $socklocation);
		} elseif (!socket_set_nonblock($this->socket)) {
			die('Unable to set nonblock mode for socket');
		}
		
		$this->socklocation = $socklocation;
		$this->server = $server;

		$this->writeData($message);
	}

	public function readData() {
		return socket_read($this->socket, 65536);
	}

	public function writeData($data) {
		$data = serialize($data);
		$length = strlen($data);

		$bytes = socket_sendto($this->socket, $data, $length, 0, $this->server);

		if (!$bytes) {
			die('Unable to send data to socket');
		}
	}

	public function waitForData() {
		if (!socket_set_block($this->socket)) {
			die('Unable to set block mode for socket');
		}

		$data = $this->readData();

		if (!socket_set_nonblock($this->socket)) {
			die('Unable to set nonblocking mode for socket');
		}
	}
}
