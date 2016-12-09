<?php
namespace Aptenex\VacayPayum\Action\Api;

use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Aptenex\VacayPayum\Request\Api\CreateToken;
use Stripe\Error;
use Stripe\Stripe;
use Stripe\Token;

class CreateTokenAction extends BaseApiAwareAction implements ApiAwareInterface
{

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CreateToken */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        try {
            Stripe::setApiKey($this->api->getSecretKey());

            $token = Token::create($model->toUnsafeArrayWithoutLocal());

            $model->replace($token->__toArray(true));
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
            $request instanceof CreateToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
