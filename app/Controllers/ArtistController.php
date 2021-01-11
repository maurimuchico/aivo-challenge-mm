<?php
namespace app\Controllers;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Endpoint Controller for retrieving information about artists from the Spotify catalog
 */
class ArtistController
{
    private $spotify_api_client;

    function __construct($container)
    {
        $this->spotify_api_client = $container->get('spotifyWebApiClient');
    }

    /**
     *
     * Returns a JSON with all discography from a given artist
     *
     * It is necessary to get the artist_id because the Spotify Web Search API
     * doesn't provide a way to search albums by artist string with exact match pattern
     *
     * @param $request q param with <band_name>
     * @param $response JSON with the discopraphy
     * @param $args route arguments (not used)
     * 
     * @return HTTP (200, JSON) response with the discography if band exists
     * @return HTTP (400, JSON) if the band name is missing, i.e. q=
     * @return HTTP (404, JSON) if the band name doesn't exist
     * 
     */
    public function getAlbums($request, $response, $args)
    {
        $band_name = $request->getQueryParam('q', $default = null);
        if (empty($band_name)) {
            $error = array(
                'error' => 'Missing band name'
            );
            return $response->withJson($error, 400);
        } else {
            $artist_id = $this->spotify_api_client->GetArtistId($band_name);
            if ($artist_id) {
                $albums = $this->spotify_api_client->GetAlbums($artist_id);
                return $response->withJson($albums);
            } else {
                $error = array(
                    'error' => 'Band not found'
                );
                return $response->withJson($error, 404);
            }
        }
    }
}