<?php

/*
 * This file is part of the doyo/behat-coverage-extension project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\RemoteSession;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoteController
{
    const SERIALIZED_OBJECT_CONTENT_TYPE = 'application/php-serialized-object';

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
        $request  = Request::createFromGlobals();
        $action   = $request->get('action').'Action';
        $callable = [$this, $action];

        if (!method_exists($this, $action)) {
            $callable = [$this, 'notFoundAction'];
        }

        return \call_user_func_array($callable, [$request]);
    }

    /**
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

        return new JsonResponse($data, Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function initAction(Request $request)
    {
        if (!$request->isMethod(Request::METHOD_POST)) {
            return $this->unsupportedMethodAction($request, 'POST');
        }
        $name   = $request->get('session');
        $config = $request->getContent();
        $config = json_decode($config, true);

        $session = new RemoteSession($name);
        $session->init($config);

        $data = [
            'message' => 'coverage session: '.$name.' initialized.',
        ];

        return new JsonResponse($data, Response::HTTP_ACCEPTED);
    }

    public function readAction(Request $request)
    {
        if (!$request->isMethod(Request::METHOD_GET)) {
            return $this->unsupportedMethodAction($request, Request::METHOD_GET);
        }

        if (!$request->get('session')) {
            $data = [
                'message' => 'code coverage session not exists',
            ];

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }
        $session = $request->get('session');
        $session = new RemoteSession($session);
        $data    = serialize($session);

        if (null === $session->getProcessor()) {
            $data = [
                'message' => 'Session '.$session->getName().' is not initialized.',
            ];

            return new JsonResponse($data, Response::HTTP_NOT_FOUND);
        }

        $response =  new Response($data, Response::HTTP_OK);
        $response->headers->set('Content-Type', static::SERIALIZED_OBJECT_CONTENT_TYPE);

        return $response;
    }
}
