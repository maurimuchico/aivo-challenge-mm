<?php
namespace app\Tools\ApiClients\Spotify;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use app\Tools\Parsers\SpotifyAlbumJSONParser;

/**
 * Gets Spotify data from Spotify Web API through GuzzleHttp using spotifyAuthorization service
 */
class SpotifyWebApiClient
{
    const SPOTIFY_API_BASE_URL = "https://api.spotify.com/v1/";
    const SPOTIFY_SEARCH_LIMIT = 50;
    /* Constant to control Spotify's API search limit */
    const SPOTIFY_OFFSET_LIMIT = 2000;
    
    private $spotify_auth;
    private $api_client;
    private $album_data_context;

    function __construct($container)
    {
        $this->spotify_auth = $container->get('spotifyAuthorization');
        $this->api_client = new Client([
            'base_uri' => self::SPOTIFY_API_BASE_URL
        ]);
        $this->album_data_context = new SpotifyDataContext(new SpotifyAlbumJSONParser());
    }

    /**
     * Returns albums data in the Endpoint spec
     * 
     * @param $artist_id Spotify artist id
     * 
     * @throws \ErrorException if the albums data cannot be gathered
     * 
     * @return array with the albums
     */
    public function GetAlbums($artist_id)
    {
        $options = [
            'query' => [
                'limit' => self::SPOTIFY_SEARCH_LIMIT
            ],
            'headers' => [
                'authorization' => 'Bearer ' . $this->spotify_auth->GetToken()
            ]
        ];
        try {
            $response = $this->api_client->request('GET', 'artists/' . $artist_id . '/albums', $options);
            $response_json = $response->getBody();
            $data = $this->album_data_context->ExecuteParse($response_json);
            $albums = $data["albums"];
            $total_records = $data["total_records"];
            $total_iterations = intdiv($total_records, self::SPOTIFY_SEARCH_LIMIT);
            for ($tit = 1; $tit <= $total_iterations; $tit ++) {
                $offset = self::SPOTIFY_SEARCH_LIMIT * $tit;
                $options = [
                    'query' => [
                        'limit' => self::SPOTIFY_SEARCH_LIMIT,
                        'offset' => $offset
                    ],
                    'headers' => [
                        'authorization' => 'Bearer ' . $this->spotify_auth->GetToken()
                    ]
                ];
                $response = $this->api_client->request('GET', 'artists/' . $artist_id . '/albums', $options);
                $response_json = $response->getBody();
                $data = $this->album_data_context->ExecuteParse($response_json);
                array_push($albums, $data["albums"]);
            }
            return $albums;
        } catch (RequestException $e) {
            throw new \ErrorException("Cannot get album data");
        }
    }

    /**
     * Returns the artist id based on the artist name.
     *
     * @param $artist_name artist string name
     *
     * @throws \ErrorException if the artist data cannot be gathered
     *
     * @return Spotify artist_id or 0 if it couldn't be found 
     */
    public function GetArtistID($artist_name)
    {
        $options = [
            'query' => [
                'q' => $artist_name,
                'type' => 'artist',
                'limit' => self::SPOTIFY_SEARCH_LIMIT
            ],
            'headers' => [
                'authorization' => 'Bearer ' . $this->spotify_auth->GetToken()
            ]
        ];
        try {
            $response = $this->api_client->request('GET', 'search', $options);
            $response_obj = json_decode($response->getBody());
			//For efficiency reasons the artist name was not extracted by the strategy pattern
            $artist_id = $this->FilterArtistID($response_obj->artists->items, $artist_name);
            $total_records = $response_obj->artists->total;
            $total_iterations = intdiv($total_records, self::SPOTIFY_SEARCH_LIMIT);
            for ($tit = 1; $tit <= $total_iterations && ! $artist_id; $tit ++) {
                $offset = self::SPOTIFY_SEARCH_LIMIT * $tit;
                if ($offset == self::SPOTIFY_OFFSET_LIMIT) {
                    //Practically non reachable condition, implemented for endpoint safety
                    break;
                }
                $options = [
                    'query' => [
                        'q' => $artist_name,
                        'type' => 'artist',
                        'limit' => self::SPOTIFY_SEARCH_LIMIT,
                        'offset' => $offset
                    ],
                    'headers' => [
                        'authorization' => 'Bearer ' . $this->spotify_auth->GetToken()
                    ]
                ];
                $response = $this->api_client->request('GET', 'search', $options);
                $response_obj = json_decode($response->getBody());
                $artist_id = $this->FilterArtistID($response_obj->artists->items, $artist_name);
            }
            return $artist_id;
        } catch (RequestException $e) {
            throw new \ErrorException("Cannot get artist data");
        }
    }

    /* Searches for the first result in the array of artists items whose name matches $artist_name */
    private function FilterArtistID($arr_artist_items, $artist_name)
    {
        foreach ($arr_artist_items as $item) {
            if (strcasecmp($item->name, $artist_name) == 0) {
                return $item->id;
            }
        }
        return 0;
    }
}