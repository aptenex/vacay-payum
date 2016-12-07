<?php
namespace PAptenex\VacayPayum\Action\Api;

use Aptenex\VacayPayum\Action\Api\BaseApiAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\ObtainCreditCard;
use Aptenex\VacayPayum\Constants;
use Aptenex\VacayPayum\Request\Api\CreateTokenForCreditCard;
use Aptenex\VacayPayum\Request\Api\ObtainToken;

class ObtainTokenForCreditCardAction extends BaseApiAwareAction implements ApiAwareInterface
{

    use ApiAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request ObtainToken */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());
        if ($model['card']) {
            throw new LogicException('Payment already has token set');
        }
        
        $obtainCreditCard = new ObtainCreditCard($request->getToken());
        $obtainCreditCard->setModel($request->getFirstModel());
        $obtainCreditCard->setModel($request->getModel());
        $this->gateway->execute($obtainCreditCard);
        $card = $obtainCreditCard->obtain();

        $local = $model->getArray('local');
        
        $createTokenForCreditCard = new CreateTokenForCreditCard($card);
        $createTokenForCreditCard->setToken((array) $local->getArray('token'));
        
        $this->gateway->execute($createTokenForCreditCard);
        $token = ArrayObject::ensureArrayObject($createTokenForCreditCard->getToken());
        
        $local['token'] = $token->toUnsafeArray();
        $model['local'] = (array) $local;

        if ($token['id']) {
            $model['card'] = $token['id']; 
        } else {
            $model['status'] = Constants::STATUS_FAILED;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof ObtainToken &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
