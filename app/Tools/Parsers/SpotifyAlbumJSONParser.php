<?php
namespace app\Tools\Parsers;

/**
 * Implements a concrete strategy to convert Spotify Web API Albums to the following format:
 * [{
 *   "name": "Album Name",
 *   "released": "10-10-2010",
 *    "tracks": 10,
 *    "cover": {
 *        "height": 640,
 *        "width": 640,
 *        "url": "https://i.scdn.co/image/6c951f3f334e05ffa"
 *    }
 * },
 * ...
 * ]
 */
class SpotifyAlbumJSONParser implements JSONParser
{

    /**
     * 
     * {@inheritDoc}
     * 
     * @see \app\Tools\Parsers\JSONParser::Parse()
     * 
     * @return array with total_records for pagination and albums in endpoint format
     * 
     */
    public function Parse($obj_data): array
    {
        $data = array();
        $data["total_records"] = $obj_data->total;
        $data["albums"] = array();
        $arr_albums = $obj_data->items;
        foreach ($arr_albums as $item) {
            $name = $item->name;
            $released = $item->release_date;
            $released = date_create($released);
            $released = date_format($released, 'd-m-Y');
            $tracks = $item->total_tracks;
            $images = $item->images;
            $cover = array();
            if (! empty($images)) {
                $cover = $images[0];
            }
            array_push($data["albums"], [
                'name' => $name,
                'released' => $released,
                'tracks' => $tracks,
                'cover' => $cover
            ]);
        }
        return $data;
    }
}