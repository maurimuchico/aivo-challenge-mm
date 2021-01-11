<?php
$container = $app->getContainer();

// Custom Services Registration
$container['spotifyAuthorization'] = function ($c) {
    return app\Tools\ApiClients\Spotify\SpotifyAuthorization::GetInstance();
};

$container['spotifyWebApiClient'] = function ($c) {
    return new app\Tools\ApiClients\Spotify\SpotifyWebApiClient($c);
};

// App Controllers Registration
$container['ArtistController'] = function ($c) {
    return new app\Controllers\ArtistController($c);
};

// Custom error handlers to return system readable endpoint error messages
$container['notFoundHandler'] = function ($c) {
    return new app\Exceptions\NotFoundHandler($c);
};

$container['errorHandler'] = function ($c) {
    return new app\Exceptions\ErrorHandler($c);
};