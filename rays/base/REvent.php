<?php
/**
 * REvent base class
 *
 * @author: Raysmond
 */

class REvent
{
    /**
     * The event render object
     * @var Object
     */
    private $sender;

    /**
     * Parameters passed to the event
     * @var array|mixed
     */
    private $params;

    public function __construct($sender, $params)
    {
        $this->sender = $sender;
        $this->params = $params;
    }

    /**
     * Get the sender of the event
     * @return Object
     */
    public function getSender(){
        return $this->sender;
    }

    /**
     * Get the event parameters
     * @return array|mixed
     */
    public function getParams(){
        return $this->params;
    }

}