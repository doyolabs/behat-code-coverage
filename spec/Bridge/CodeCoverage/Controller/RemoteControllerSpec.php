<?php

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller\RemoteController;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use PhpSpec\ObjectBehavior;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use spec\Doyo\Behat\Coverage\ResponseTrait;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RemoteControllerSpec extends ObjectBehavior
{
    use ResponseTrait;

    public function it_is_initializable()
    {
        $this->shouldHaveType(RemoteController::class);
    }

    public function its_create_should_create_a_new_instance()
    {
        $this->create()->shouldHaveType(RemoteController::class);
    }

    public function its_should_return_404_when_action_not_exist(
        Request $request
    ) {
        $request->get('action')->willReturn('foo');

        $response = $this->getResponse();
        $response->shouldBeInJson();
        $response->shouldContainJsonKey('message');
        $response->shouldContainJsonKeyWithValue('message', 'The page you requested');
    }

    public function its_notFoundAction_should_handle_404_response(
        Request $request
    ) {
        $response = $this->notFoundAction();
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(404);
    }

    public function its_methodUnsupported_should_handle_unsupported_method(
        Request $request
    ) {
        $request->get('action')->willReturn('action');
        $request->getMethod()->willReturn('GET');
        $response = $this->unsupportedMethodAction($request, Request::METHOD_POST);
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function its_initAction_only_accept_post_method(
        Request $request
    ) {
        $request->isMethod('POST')->willReturn(false);
        $request->getMethod()->willReturn('GET');
        $request->get('action')->willReturn('init');

        $response = $this->initAction($request);
        $response->shouldBeInJson();
    }

    public function its_initAction_should_init_new_coverage_session(
        Request $request
    ) {
        $config = [
            'filterOptions' => [
                'whitelistedFiles' => [
                    __FILE__,
                ],
            ],
            'codeCoverageOptions' => [
                'addUncoveredFilesFromWhitelist' => true,
            ],
        ];

        $data = json_encode($config);

        $request->get('session')->shouldBeCalled()->willReturn('spec-remote');
        $request->getContent()->willReturn($data);
        $request->isMethod('POST')->shouldBeCalled()->willReturn(true);
        $request->headers = new HeaderBag();

        $response = $this->initAction($request);
        $response->shouldBeInJson();
        $response->shouldContainJsonKey('message');
        $response->shouldContainJsonKeyWithValue('message', 'coverage session: spec-remote initialized.');
        $response->shouldHaveStatusCode(Response::HTTP_ACCEPTED);
    }

    public function its_readAction_only_accept_get_method(
        Request $request
    ) {
        $request->get('action')->willReturn('read');
        $request->getMethod()->willReturn('POST');
        $request->isMethod('GET')->willReturn(false);

        $response = $this->readAction($request);
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function its_readAction_will_not_process_undefined_session(
        Request $request
    ) {
        $request->isMethod('GET')->willReturn(true);
        $request->get('session')->willReturn(null);

        $response = $this->readAction($request);
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(404);
    }

    public function its_readAction_will_not_process_uninitialized_session(
        Request $request
    ) {
        $request->isMethod('GET')->willReturn(true);
        $request->get('session')->willReturn('uninitialized');

        $response = $this->readAction($request);
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(404);
    }

    public function its_readAction_returns_code_coverage_data(
        Request $request,
        ProcessorInterface $processor,
        SessionInterface $session
    ) {
        $codeCoverage  = new CodeCoverage(new Dummy());
        $processor->getCodeCoverage()->willReturn($codeCoverage);
        $session->getProcessor()->willReturn($processor);
        $processor->getCodeCoverage()->willReturn($codeCoverage);

        $request->isMethod('GET')->willReturn(true);
        $request
            ->get('session')
            ->willReturn('spec-remote')
            ->shouldBeCalled();

        $response = $this->readAction($request);
        $response->shouldBeAHttpResponse();
        $response->shouldBeASerializedObject(SessionInterface::class);
    }
}
