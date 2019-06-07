<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\OldSession;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller\RemoteController;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\RemoteSession;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

class RemoteControllerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RemoteController::class);
    }

    function its_create_should_create_a_new_instance()
    {
        $this->create()->shouldHaveType(RemoteController::class);
    }

    public function getMatchers(): array
    {
        return [
            'beInJson' => function($subject){
                Assert::isInstanceOf($subject,JsonResponse::class);
                return true;
            },
            'containJsonKey' => function($subject, $key){
                /* @var \Symfony\Component\HttpFoundation\JsonResponse $subject */
                $json = $subject->getContent();
                $json = json_decode($json, true);
                Assert::isArray($json);
                Assert::keyExists($json,$key);

                return true;
            },
            'containJsonKeyWithValue' => function($subject, $key, $expected){
                /* @var \Symfony\Component\HttpFoundation\JsonResponse $subject */
                $json = $subject->getContent();
                $json = json_decode($json, true);
                Assert::keyExists($json,$key);
                Assert::contains($json[$key],$expected);

                return true;
            },
            'haveStatusCode' => function($subject, $expected){
                Assert::eq($subject->getStatusCode(), $expected);
                return true;
            },
            'haveContent' => function($subject, $expected){
                Assert::isInstanceOf($subject, Response::class);
                Assert::contains($subject->getContent(), $expected);

                return true;
            }
        ];
    }

    function its_should_return_404_when_action_not_exist(
        Request $request
    )
    {
        $request->get('action')->willReturn('foo');

        $response = $this->getResponse();
        $response->shouldBeInJson();
        $response->shouldContainJsonKey('message');
        $response->shouldContainJsonKeyWithValue('message','The page you requested');
    }

    function its_notFoundAction_should_handle_404_response(
        Request $request
    )
    {
        $response = $this->notFoundAction();
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(404);
    }

    function its_methodUnsupported_should_handle_unsupported_method(
        Request $request
    )
    {
        $request->get('action')->willReturn('action');
        $request->getMethod()->willReturn('GET');
        $response = $this->unsupportedMethodAction($request, Request::METHOD_POST);
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
    }


    function its_initAction_only_accept_post_method(
        Request $request
    )
    {
        $request->isMethod('POST')->willReturn(false);
        $request->getMethod()->willReturn('GET');
        $request->get('action')->willReturn('init');

        $response = $this->initAction($request);
        $response->shouldBeInJson();
    }

    function its_initAction_should_init_new_coverage_session(
        Request $request
    )
    {
        $config = [
            'filterOptions' => [
                'whitelistedFiles' => [
                    __FILE__
                ]
            ],
            'codeCoverageOptions' => [
                'addFilesToWhiteList' => true
            ]
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

    function its_readAction_only_accept_get_method(
        Request $request
    )
    {
        $request->get('action')->willReturn('read');
        $request->getMethod()->willReturn('POST');
        $request->isMethod('GET')->willReturn(false);

        $response = $this->readAction($request);
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    function its_readAction_returns_code_coverage_data(
        Request $request
    )
    {
        $session = new RemoteSession('spec-remote');
        $session->setData($data = ['data' => 'coverage-data']);
        $session->save();

        $request->isMethod('GET')->willReturn(true);
        $request
            ->get('session')
            ->willReturn('spec-remote')
            ->shouldBeCalled()
        ;

        $response = $this->readAction($request);
        $response->shouldBeInJson();
        $response->shouldHaveStatusCode(Response::HTTP_OK);
        $response->shouldHaveContent('{"data":"coverage-data"}');
    }
}
