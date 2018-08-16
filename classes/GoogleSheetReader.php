<?php

namespace Classes;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_Sheet;
use Google_Service_Sheets_RowData;
use Google_Service_Sheets_BatchGetValuesResponse;

class GoogleSheetReader{
	protected $data;
	function __construct() {

	}

	function auth(){
		$client = new Google_Client();
		$client->setApplicationName('Google Sheet To Data');
		$client->setScopes(Google_Service_Sheets::SPREADSHEETS_READONLY);
		$client->setAuthConfig(VTM_WPI_ASSETS_PATH.'/credentials.json');
		$client->setAccessType('offline');

		// Load previously authorized credentials from a file.
		$credentialsPath = VTM_WPI_ASSETS_PATH.'/token.json';
		if (file_exists($credentialsPath)) {
			$accessToken = json_decode(file_get_contents($credentialsPath), true);
		} else {
			// Request authorization from the user.
			$authUrl = $client->createAuthUrl();
			printf("Open the following link in your browser:\n%s\n", $authUrl);
			print 'Enter verification code: ';
			$authCode = trim(fgets(STDIN));

			// Exchange authorization code for an access token.
			$accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

			// Store the credentials to disk.
			if (!file_exists(dirname($credentialsPath))) {
				mkdir(dirname($credentialsPath), 0700, true);
			}
			file_put_contents($credentialsPath, json_encode($accessToken));
			printf("Credentials saved to %s\n", $credentialsPath);
		}
		$client->setAccessToken($accessToken);

		// Refresh the token if it's expired.
		if ($client->isAccessTokenExpired()) {
			$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
			file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
		}
		return $client;
	}

	function convertToArray(){
		$data = [];
		$sample = [];
		foreach ($this->data as $i => $item){
			if ($i > 0){
				$tmp = [];
				foreach ($sample as $j => $key){
					$tmp[$key] = $item[$j];
				}
				array_push($data, $tmp);
			}
			else{
				$sample = $item;
			}
		}
		$this->data = $data;
	}

	function read($id, $range = "A:B"){
		$client = $this->auth();
		$service = new Google_Service_Sheets($client);
		$result = $service->spreadsheets_values->get($id, $range);
		$this->data =  $result->getValues();
		$this->convertToArray();
		return $this->data;
	}
}