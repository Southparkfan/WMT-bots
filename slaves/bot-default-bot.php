<?php
require_once __DIR__ . '../includes/Autoloader.php';

$botchannel = '#wmt-delete.php';
$freenodeircnick .= '-9';

$sock = new SocketReader($sockdir . 'wmt-delete.sock', $sockdir . 'server.sock', $arraysock);

$irc = new IRCClient($freenodeircserver, $freenodeircport, $freenodeircnick, $freenodeircusername);
$irc->identify($freenodeircpassword);
sleep(2);
$irc->join($botschannel);
$irc->join($botchannel);

sleep(1);
$irc->say($botschannel, "I just restarted and joined $botchannel with success.");

while (true) {
	if ($data = $irc->getData()) {
		$explode = explode(' ', $data);

		if (!empty($data)) {
			if ($explode[0] == 'PING') {
				$irc->sendData('PONG ' . $explode[1]);
				echo "PING PONG OK\n";
			} elseif ($explode[0] == 'ERROR') {
				exit(3);
			}
		}
	} elseif ($data = $sock->getData()) {
		// DO preg_match STUFF
	} else {
		usleep(500000); // Wait 0.5 seconds
	}
	
	unset($data);
}
