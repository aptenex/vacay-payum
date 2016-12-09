<?php
namespace Aptenex\VacayPayum;

use Http\Message\MessageFactory;
use Payum\Core\Exception\Http\HttpException;
use Payum\Core\HttpClientInterface;
use Psr\Http\Message\ResponseInterface;

class Api
{

    /**
     * @var Keys
     */
    protected $keys;

    /**
     * @var HttpClientInterface
     */
    protected $client;

    /**
     * @var MessageFactory
     */
    protected $messageFactory;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param array               $options
     * @param HttpClientInterface $client
     * @param MessageFactory      $messageFactory
     *
     * @throws \Payum\Core\Exception\InvalidArgumentException if an option is invalid
     */
    public function __construct(array $options, HttpClientInterface $client, MessageFactory $messageFactory)
    {
        $this->options = $options;
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->keys = new Keys($options['apiKey'], $options['accountUuid'], $options['publishableKey']);
    }

    public function createCharge(array $model)
    {
        $d = [
            'amount'                   => $model['amount'],
            'currency'                 => strtoupper($model['currency']),
            'cardToken'                => $model['token'],
            'description'              => isset($model['description']) ? $model['description'] : null,
            'authorize'                => isset($model['authorize']) ? $model['authorize'] : false,
            'sendEmailConfirmation'    => isset($model['sendEmailConfirmation']) ? $model['sendEmailConfirmation'] : false,
            'email'                    => isset($model['email']) ? $model['email'] : null,
            'firstName'                => isset($model['firstName']) ? $model['firstName'] : null,
            'lastName'                 => isset($model['lastName']) ? $model['lastName'] : null,
            'externalBookingReference' => isset($model['externalBookingReference']) ? $model['externalBookingReference'] : null,
            'externalPaymentReference' => isset($model['externalPaymentReference']) ? $model['externalPaymentReference'] : null,
            'meta'                     => isset($model['meta']) ? $model['meta'] : null,
        ];

        $endpoint = sprintf('/vacay-pay/accounts/%s/payments', $this->getKeys()->getAccountUuid());

        $response = $this->doRequest($endpoint, 'POST', $d);
    }

    /**
     * @param string $endpoint
     * @param string $method
     * @param array  $fields
     *
     * @return ResponseInterface
     */
    protected function doRequest($endpoint, $method, array $fields)
    {
        $headers = [
            'Content-Type' => 'application/json',
            'X-Auth-Token' => $this->getKeys()->getApiKey()
        ];

        $request = $this->messageFactory->createRequest(
            $method,
            $this->getApiBaseUrl().$endpoint,
            $headers,
            json_encode($fields)
        );

        $response = $this->client->send($request);

        if (false == ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300)) {
            throw HttpException::factory($request, $response);
        }

        return $response;
    }

    /**
     * @return string
     */
    protected function getApiBaseUrl()
    {
        return 'https://www.procuro.io/api/v1';
    }

    /**
     * @return Keys
     */
    public function getKeys()
    {
        return $this->keys;
    }

}
