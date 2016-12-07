<?php
namespace Aptenex\VacyPayum\Action\Api;

use Aptenex\VacayPayum\Action\Api\BaseApiAwareAction;
use Aptenex\VacayPayum\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Aptenex\VacayPayum\Request\Api\CreateCharge;
use Stripe\Charge;
use Stripe\Error;
use Stripe\Stripe;

class CreateChargeAction extends BaseApiAwareAction implements ActionInterface, ApiAwareInterface
{

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateCharge */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == ($model['card'] || $model['customer'])) {
            throw new LogicException('The either card token or customer id has to be set.');
        }

        if (is_array($model['card'])) {
            throw new LogicException('The token has already been used.');
        }

        try {
            // USE VACAYPAY FOR THIS PART
            $charge = $this->getApi()->createCharge($model->toUnsafeArrayWithoutLocal());

            $model->replace($charge->__toArray(true));
        } catch (Error\Base $e) {
            $model->replace($e->getJsonBody());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CreateCharge &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
