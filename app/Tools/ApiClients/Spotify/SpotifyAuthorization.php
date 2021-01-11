<?php
namespace app\Tools\ApiClients\Spotify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use app\Tools\Storage;

/**
 * Provides a Singleton Interface to the Spotify Client Credentials Flow
 * This flow grants access to non-user Spotify data using GuzzleHttp
 * 
 * The Access Token returned by Spotify is stored in secondary storage to avoid re-request on valid tokens
 */
class SpotifyAuthorization
{
    const AUTH_URL = "https://accounts.spotify.com/api/token";
    /* Credentials were hardcoded for simplicity but they could be stored in persistent encrypted storage */
    const ENCODED_CREDENTIALS = "MjY2YjExMTU0NThhNDZlZWI4MTEwNTRmYmFlZjIyNzk6OTM1NThjNjQ2ZGZiNGM2Njg4MjlkZTUxYzY0MDRmMDk=";
    const DATA_KEY = "token";
    
    private static $instance;
    
    private $auth_client;

    private function __construct()
    {
        $this->auth_client = new Client();
    }

    public static function GetInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /*
     * Initialises the token, sends it to permanent storage and returns it
     */
    private function InitToken()
    {
        $options = [
            'form_params' => [
                'grant_type' => 'client_credentials'
            ],
            'headers' => [
                'authorization' => 'Basic ' . self::ENCODED_CREDENTIALS
            ]
        ];

        try {
            $response = $this->auth_client->request('POST', self::AUTH_URL, $options);
            $json_response = json_decode($response->getBody());
            $token_data = [
                'access_token' => $json_response->access_token,
                'access_token_duration' => $json_response->expires_in,
                'access_token_timestamp' => time()
            ];
            Storage::SaveDataByKey(self::DATA_KEY, $token_data);
            return $json_response->access_token;
        } catch (RequestException $e) {
            throw new \ErrorException("Cannot get Spotify access token");
        }
    }

    /**
     * Gets the token from secondary storage 
     * Checks if the token is valid. If it is not, requests a new token
     * Returns the access_token
     */
    public function GetToken()
    {
        $token_data = Storage::GetDataByKey(self::DATA_KEY);
        if (is_null($token_data)) {
            return self::$instance->InitToken();
        } else {
            $current_timestamp = time();
            $token_timestamp_offset = $current_timestamp - $token_data->access_token_timestamp;
            if ($token_timestamp_offset >= $token_data->access_token_duration) {
                return self::$instance->InitToken();
            } else {
                return $token_data->access_token;
            }
        }
    }
}