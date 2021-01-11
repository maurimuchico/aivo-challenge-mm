<?php
namespace app\Tools\ApiClients\Spotify;

/**
 * Implements Strategy Pattern to decouple the parsing of the different API Data Objects
 */
class SpotifyDataContext
{
    private $json_parser;

    public function __construct($json_parser)
    {
        $this->json_parser = $json_parser;
    }

    public function ExecuteParse($json_data): array
    {
        $obj_data = json_decode($json_data);
        return $this->json_parser->Parse($obj_data);
    }
}