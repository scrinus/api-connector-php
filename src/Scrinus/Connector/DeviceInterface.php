<?php
namespace Scrinus\Connector;

/**
 * Interface for scrinus device api calls.
 *
 * This component is a port of the scrinus library,
 * which is copyright scrinus GmbH, @see https://scrinus.com.
 *
 * @author David Spiola <david@scrinus.com>
 *
 */
interface DeviceInterface {

    /**
     * Get all devices available for current user
     * @link http://api.scrinus.com/help for more information
     *
     * @return array
     */
    public function listDevices();

    /**
     * Get a specific device by according identity
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity device identity
     * @return mixed
     */
    public function readDevice($identity);

    /**
     * Update a specific device by according identity
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity device identity
     * @param array $device device values
     * @return mixed
     */
    public function updateDevice($identity, array $device);

    /**
     * Reload all devices that have a specific device
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity device identity
     * @return mixed
     */
    public function reloadDevice($identity);
}

