<?php

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Cache;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoteController
{
    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        $request = Request::createFromGlobals();
        $action = $request->get('action').'Action';
        $callable = [$this,$action];

        if(!method_exists($this,$action)){
            $callable = [$this,'notFoundAction'];
        }

        return call_user_func_array($callable,[$request]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function notFoundAction()
    {
        $data = [
            'message' => 'The page you requested is not exists',
        ];
        return new JsonResponse($data, 404);
    }

    public function unsupportedMethodAction(Request $request, $supportedMethod)
    {
        $data = [
            'message' => sprintf(
                'action: %s not support method: %s. Supported method: %s',
                $request->get('action'),
                $request->getMethod(),
                $supportedMethod
            ),
        ];

        return new JsonResponse($data,Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function initAction(Request $request)
    {
        if(!$request->isMethod(Request::METHOD_POST)){
            return $this->unsupportedMethodAction($request,'POST');
        }
        $session = $request->get('session');
        $config = $request->getContent();
        $config = json_decode($config, true);

        $cache = new Cache($session);
        $cache->setFilter($config['filter']);
        $cache->setCodeCoverageOptions($config['codeCoverageOptions']);
        $cache->save();

        $data = [
            'message' => 'coverage session: '.$session.' initialized.'
        ];
        return new JsonResponse($data,Response::HTTP_ACCEPTED);
    }
}
