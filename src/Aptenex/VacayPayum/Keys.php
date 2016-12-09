<?php

namespace Aptenex\VacayPayum;

class Keys
{

    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $accountUuid;

    /**
     * @var string
     */
    protected $publishableKey;

    /**
     * @param string $apiKey
     * @param string $accountUuid
     * @param string $publishableKey
     */
    public function __construct($apiKey, $accountUuid, $publishableKey)
    {
        $this->apiKey = $apiKey;
        $this->accountUuid = $accountUuid;
        $this->publishableKey = $publishableKey;
    }

    /**
     * @return string
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @return string
     */
    public function getAccountUuid()
    {
        return $this->accountUuid;
    }

    /**
     * @return string
     */
    public function getPublishableKey()
    {
        return $this->publishableKey;
    }

}
