<?php

class SocketMaster {
	protected $socket;
	protected $socklocation;
	protected $clients = array();
	protected $nb_clients = 0;

	public function __construct($socklocation) {
		if (!extension_loaded('sockets')) {
			die('The sockets extension needs to be loaded.');
		}

		$this->socket = socket_create(AF_UNIX, SOCK_DGRAM, 0);

		if (!$this->socket) {
			die('Unable to create AF_UNIX socket');
		}

		$this->socklocation = $socklocation;
		
		if (file_exists($socklocation)) {
			unlink($socklocation);
		}
		
		if (!socket_bind($this->socket, $this->socklocation)) {
			die('Unable to bind socket to ' . $this->socklocation);
		} elseif (!socket_set_nonblock($this->socket)) {
			die('Unable to set nonblocking mode for socket');
		}

		echo 'Socket location: ' . $this->socklocation . "\n";
	}

	public function readData() {
		$buffer = '';
		$from = '';
		
		$bytes = socket_recvfrom($this->socket, $buffer, 65536, 0, $from);
		
		if ($bytes == -1) {
			die('An error occured while receiving data from the socket');
		}

		if (!empty($buf)) {
			$client = -1;

			foreach ($this->clients as $number => $clients) {
				if ($clients == $from) {
					$client = $number;
					break;
				}
			}

			if ($client == -1) {
				$this->clients[] = $from;
				$this->try[] = 0;
				$client = $this->nb_clients++;

				echo "New client connected\n";
			}

			return array('client' => $client, 'msg' => unserialize($buffer));
		}
	}

	public function writeData($client, $data) {
		$length = strlen($data);
		
		$bytes = socket_sendto($this->socket, $data, $length, 0, $this->clients[$client]);
		
		if (!$bytes) {
			if (empty($this->try[$client])) {
				$this->try[$client_no] = time();
                                echo "Ping client $client\n";
			} elseif (time() - $this->try[$client] > 100) {
				$this->dropClient($client);
				echo "Ping timeout for client $client" . "\n";
			}

			return false;
		} else {
			if (!empty($this->try[$client])) {
				unset($this->try[$client]);
				echo "Client $client is still connected\n";
			}

			return true;			
		}
	}

	public function dropClient($client) {
		unset($this->clients[$client]);
                unset($this->try[$client]);

		$this->nb_clients--;
		
		echo "Client $client disconnected\n";
	}

	public function clientExists($client) {
                return array_key_exists($client, $this->clients);
        }
}
