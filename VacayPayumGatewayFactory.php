<?php
namespace Aptenex\VacayPayum;

use Aptenex\VacayPayum\Action\AuthorizeAction;
use Aptenex\VacayPayum\Action\CancelAction;
use Aptenex\VacayPayum\Action\ConvertPaymentAction;
use Aptenex\VacayPayum\Action\CaptureAction;
use Aptenex\VacayPayum\Action\NotifyAction;
use Aptenex\VacayPayum\Action\RefundAction;
use Aptenex\VacayPayum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class SkeletonGatewayFactory extends GatewayFactory
{
    /**
     * {@inheritDoc}
     */
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'VacayPay',
            'payum.factory_title' => 'VacayPay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.authorize' => new AuthorizeAction(),
            'payum.action.refund' => new RefundAction(),
            'payum.action.cancel' => new CancelAction(),
            'payum.action.notify' => new NotifyAction(),
            'payum.action.status' => new StatusAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
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
    }
}
