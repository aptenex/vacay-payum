<?php
namespace Aptenex\VacayPayum\Action\Api;

use Aptenex\VacayPayum\Request\Api\ObtainToken;
use Payum\Core\ApiAwareInterface;
use Payum\Core\ApiAwareTrait;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\GetHttpRequest;
use Payum\Core\Request\RenderTemplate;

class ObtainTokenAction extends BaseApiAwareAction implements ApiAwareInterface
{

    use ApiAwareTrait;

    /**
     * @var string
     */
    protected $templateName;

    /**
     * @param string $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request ObtainToken */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());
        if ($model['card']) {
            throw new LogicException('The token has already been set.');
        }
        $getHttpRequest = new GetHttpRequest();
        $this->gateway->execute($getHttpRequest);
        if ($getHttpRequest->method == 'POST' && isset($getHttpRequest->request['vacaypayToken'])) {
            $model['cardToken'] = $getHttpRequest->request['vacaypayToken'];
            return;
        }
        $this->gateway->execute($renderTemplate = new RenderTemplate($this->templateName, array(
            'model' => $model,
            'publishable_key' => $this->keys->getPublishableKey(),
            'actionUrl' => $request->getToken() ? $request->getToken()->getTargetUrl() : null,
        )));
        throw new HttpResponse($renderTemplate->getResult());
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