<?php
namespace Aptenex\VacayPayum\Action;

use Aptenex\VacayPayum\Constants;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Request\GetStatusInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;

class StatusAction implements ActionInterface
{
    /**
     * {@inheritDoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model['meta']['errors']) {
            $request->markFailed();

            return;
        }

        if ($model['data']['refunded']) {
            $request->markRefunded();

            return;
        }

        if ($model['data']['status'] === Constants::STATUS_FAILED) {
            $request->markFailed();

            return;
        }

        if ($model['data']['status'] === Constants::STATUS_SUCCEEDED && $model['data']['paid']) {
            $request->markCaptured();

            return;
        }

        if ($model['data']['status'] === Constants::STATUS_PAID && $model['data']['paid']) {
            $request->markCaptured();

            return;
        }

        if ($model['data']['status'] === Constants::STATUS_SUCCEEDED && !$model['data']['captured']) {
            $request->markAuthorized();

            return;
        }

        if ($model['data']['status'] === Constants::STATUS_PAID && !$model['data']['captured']) {
            $request->markAuthorized();

            return;
        }

        $request->markUnknown();
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
