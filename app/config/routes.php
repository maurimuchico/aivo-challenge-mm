<?php

/* App routes are grouped to avoid future prefix replication */
$app->group('/api/v1', function () {
    $this->map([
        'GET'
    ], '/albums', 'app\Controllers\ArtistController:getAlbums');
});