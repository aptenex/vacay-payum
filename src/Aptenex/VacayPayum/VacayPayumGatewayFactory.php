<?php
namespace Aptenex\VacayPayum;

use Aptenex\VacayPayum\Action\Api\ObtainTokenAction;
use Aptenex\VacayPayum\Action\AuthorizeAction;
use Aptenex\VacayPayum\Action\CancelAction;
use Aptenex\VacayPayum\Action\ConvertPaymentAction;
use Aptenex\VacayPayum\Action\CaptureAction;
use Aptenex\VacayPayum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class VacayPayumGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'vacaypay',
            'payum.factory_title' => 'VacayPay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            //'payum.action.get_credit_card_token' => new GetCreditCardTokenAction(),
            'payum.action.obtain_token' => function (ArrayObject $config) {
                return new ObtainTokenAction($config['payum.template.obtain_token']);
            },
            'payum.template.obtain_token' => '@PayumStripe/Action/obtain_js_token.html.twig',
        ]);

        if ($config['payum.api'] == false) {
            $config['payum.default_options'] = [];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['apiKey', 'accountUuid', 'publishableKey'];

            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);

                return new Api((array) $config, $config['payum.http_client'], $config['httplug.message_factory']);
            };
        }

        $config['payum.paths'] = array_replace([
            'VacayPayum' => __DIR__ . '/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
