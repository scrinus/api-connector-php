<?php
namespace Scrinus\Connector;

/**
 * abstract calls for scrinus device api calls.
 *
 * This component is a port of the scrinus library,
 * which is copyright scrinus GmbH, @see https://scrinus.com.
 *
 * @author David Spiola <david@scrinus.com>
 *
 */
class AbstractAdapter implements DeviceInterface, PlaylistInterface, MessageInterface {

    /**
     * @var HttpClient
     */
    protected $api;

    /**
     * Get all devices available for current user
     * @link http://api.scrinus.com/help for more information
     *
     * @return array
     */
    public function listDevices() {
        $response = $this->api->get('/device');

        return $response->success ? $response->data : array();
    }

    /**
     * Get a specific device by according identity
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity device identity
     * @return mixed
     */
    public function readDevice($identity) {
        $response = $this->api->get('/device/' . $identity);

        return $response->success ? $response->data : array();
    }

    /**
     * Update a specific device by according identity
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity device identity
     * @param array $device device values
     * @return mixed
     */
    public function updateDevice($identity, array $device) {
        $response = $this->api->put('/device/' . $identity, array('device' => $device));

        return $response->success ? $response->data : array();
    }

    /**
     * Reload all devices that have a specific device
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity device identity
     * @return mixed
     */
    public function reloadDevice($identity) {
        $response = $this->api->get('/device/reload/' . $identity);

        return $response->success ? $response->data : array();
    }

    /**
     * Get all playlists available for current user
     * @link http://api.scrinus.com/help for more information
     * @return mixed
     */
    public function listPlaylists() {
        $response = $this->api->get('/playlist');

        return $response->success ? $response->data : array();
    }

    /**
     * Get a specific playlist by according identity
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity playlist identity
     * @return mixed
     */
    public function readPlaylist($identity) {
        $response = $this->api->get('/playlist/' . $identity);

        return $response->success ? $response->data : array();
    }

    /**
     * Create a new playlist
     * @link http://api.scrinus.com/help for more information
     *
     * @param array $playlist playlist values
     * @return mixed
     */
    public function createPlaylist(array $playlist) {
        $response = $this->api->post('/playlist', array('playlist' => $playlist));

        return $response->success ? $response->data : array();
    }

    /**
     * Update a specific playlist by according identity
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity playlist identity
     * @param array $playlist playlist values
     * @return mixed
     */
    public function updatePlaylist($identity, array $playlist) {
        $response = $this->api->put('/playlist/' . $identity, array('playlist' => playlist));

        return $response->success ? $response->data : array();
    }

    /**
     * Send message to a specific device
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity
     * @param array $message
     * @return mixed
     */
    public function sendMessageToDevice($identity, array $message)
    {
        $response = $this->api->post('/flashmessage', array('message' => $message));

        return $response->success ? $response->data : array();
    }
}