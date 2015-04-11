<?php
class HTTP {
	protected $ch;
	public $useragent;

	public function __construct($useragent) {
		$this->ch = curl_init();
		$this->useragent = ($useragent) ? $useragent : 'WMT Bots/1.1';
	}

	public function get($url) {
		curl_setopt_array($ch,
			array(
				CURLOPT_URL => $url,
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_HEADER => 0,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_HTTPGET => 1
			)
		);
	
		$data = curl_exec($this->ch);
		
		if ($data === false) {
			return "Curl couldn't GET '$url': " . curl_error($this->ch);
		}
		
		return $data;
	}

	public function post($url, $data) {
		curl_setopt_array($ch,
                        array(
                                CURLOPT_URL => $url,
                                CURLOPT_MAXREDIRS => 10,
                                CURLOPT_HEADER => 0,
                                CURLOPT_RETURNTRANSFER => 1,
                                CURLOPT_TIMEOUT => 30,
                                CURLOPT_CONNECTTIMEOUT => 10,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $data
			)
		);

		if ($data === false) {
                        return "Curl couldn't POST '$url': " . curl_error($this->ch);
                }

                return $data;

	}
}
