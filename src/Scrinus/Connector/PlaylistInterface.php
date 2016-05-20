<?php
namespace Scrinus\Connector;

/**
 * Interface for scrinus playlist api calls.
 *
 * This component is a port of the scrinus library,
 * which is copyright scrinus GmbH, @see https://scrinus.com.
 *
 * @author David Spiola <david@scrinus.com>
 *
 */
interface PlaylistInterface {
    /**
     * Get all playlists available for current user
     * @link http://api.scrinus.com/help for more information
     * @return array
     */
    public function listPlaylists();

    /**
     * Get a specific playlist by according identity
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity playlist identity
     * @return mixed
     */
    public function readPlaylist($identity);

    /**
     * Create a new playlist
     * @link http://api.scrinus.com/help for more information
     *
     * @param array $playlist playlist values
     * @return mixed
     */
    public function createPlaylist(array $playlist);

    /**
     * Update a specific playlist by according identity
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity playlist identity
     * @param array $playlist playlist values
     * @return mixed
     */
    public function updatePlaylist($identity, array $playlist);
}

