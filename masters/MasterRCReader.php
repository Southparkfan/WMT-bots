<?php
require_once __DIR__ . '/../includes/Autoloader.php';
require_once __DIR__ . '/../configuration.php';
require_once __DIR__ . '/../wikisets.php';

$socket = new SocketMaster('/data/project/wmt/bots/tmp/sockets/server.sock');

$dbsettings = parse_ini_file('/data/project/wmt/replica.my.cnf');
$db = new mysqli('metawiki.labsdb', $dbsettings['user'], $dbsettings['password'], 'meta_p');

$res = $mysql->query("SELECT `dbname`, `slice`, `url` FROM `wiki` WHERE `is_closed` = 0 ORDER BY `slice`");
$res = array_diff($res, 'centralauth');

$channels = array();
$databases = array();

foreach ($res as $wiki) {
	$wiki['url'] = str_replace(array('http://', 'https://'), array('', ''), $wiki['url']);

	preg_match('/^([a-z]+)\.([a-z]+)\./', $wiki['url'], $matches);
	
	if (!empty($matches[1])) {
		if ($wiki['url'] == "www.mediawiki.org" ) {
			$channels["#mediawiki.wikipedia"] = $line['url'];
		} elseif ($line['domain'] == "www.wikidata.org") {
			$channels["#wikidata.wikipedia"] = $wiki['url'];
		} else {
			$channels["#" . $matches[1] . '.' . $matches[2]] = $line['domain'];
		}
		
		$databases[$wiki['slice']][$wiki['dbname']] = $wiki['domain'];
	}

}

$res->free();
$db->close();
ksort($channels, SORT_STRING);

$irc = new IRCClient($wmircserver, $wmircport, $wmircuser, '');


foreach ($channels as $channel => $domain ) {
	$irc->sendData("JOIN $channel");
	usleep( 30000 ); # hack (prevents from being kicked for excess flood)
}

$clients = array();

while (true) {
	if ($data = $irc->getData()) {
		$explode = explode(' ', $data);
		
		if (!empty($data)) {
			if ($explode[0] == 'PING') {
				$irc->sendData('PONG ' . $explode[1]);
				echo "PING PONG OK\n";
			} elseif ($explode[0] == 'ERROR') {
				$irc->sendData('QUIT');
				exit(3);
			} elseif (preg_match('/^:rc-pmtpa!~rc-pmtpa@special.user PRIVMSG #([a-z0-9]+\.[a-z0-9]+) (.+)$/', $data, $matches)) {
				$wiki = $matches[1];
				$feed = $channels['#' . $matches[1]] . ' ' . $matches[2];
				
				foreach ($clients as $client => $data) {
					if ($socket->clientExists($client)) {
						if (in_array('#' . $wiki, $wikisets[$data['wikiset']])) {
							$socket->writeData($client, $feed);
						}
					} else {
						unset($clients[$client]);
					}
				}
			}
		} 
	} else {
		$data = $socket->readData();
		
		if (!empty($data)) {
			$client = $data['client'];
			$message = $data['msg'];

			if (!array_key_exists($client, $clients)) {
				$clients[$client] = $message;
			}
		} else {
			usleep(500000); // Wait 0.5 seconds
		}
	}
	unset($data);		
}
