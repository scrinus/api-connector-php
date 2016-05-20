<?php
namespace Scrinus\Connector;

/**
 * Interface for scrinus message api calls.
 *
 * This component is a port of the scrinus library,
 * which is copyright scrinus GmbH, @see https://scrinus.com.
 *
 * @author David Spiola <david@scrinus.com>
 *
 */
interface MessageInterface {

    /**
     * Send message to a specific device
     * @link http://api.scrinus.com/help for more information
     *
     * @param string $identity
     * @param array $message
     * @return mixed
     */
    public function sendMessageToDevice($identity, array $message);
}

