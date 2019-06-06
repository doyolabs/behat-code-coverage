<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller\RemoteController;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
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

    function its_should_return_404_when_action_not_exist(
        Request $request
    )
    {
        $request->get('action')->willReturn('foo');

        $response = $this->getResponse();
        $response->shouldBeAJsonResponse();
        $response->shouldContainJsonKey('message');
        $response->shouldContainJsonKeyWithValue('message','The page you requested');
    }

    function its_notFoundAction_should_handle_404_response(
        Request $request
    )
    {
        $response = $this->notFoundAction();
        $response->shouldBeAJsonResponse();
        $response->shouldHaveStatusCode(404);
    }

    function its_methodUnsupported_should_handle_unsupported_method(
        Request $request
    )
    {
        $request->get('action')->willReturn('action');
        $request->getMethod()->willReturn('GET');
        $response = $this->unsupportedMethodAction($request, Request::METHOD_POST);
        $response->shouldBeAJsonResponse();
        $response->shouldHaveStatusCode(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    function its_initAction_should_init_new_coverage_session(
        Request $request
    )
    {
        $data = <<<EOC
{
    "filter": {},
    "codeCoverageOptions": {}
}
EOC;
        $request->get('session')->shouldBeCalled()->willReturn('spec-remote');
        $request->getContent()->willReturn($data);
        $request->isMethod('POST')->shouldBeCalled()->willReturn(true);


        $response = $this->initAction($request);
        $response->shouldBeAJsonResponse();
        $response->shouldContainJsonKey('message');
        $response->shouldContainJsonKeyWithValue('message', 'coverage session: spec-remote initialized.');
        $response->shouldHaveStatusCode(Response::HTTP_ACCEPTED);
    }

    public function getMatchers(): array
    {
        return [
            'beAJsonResponse' => function($subject){
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
            }
        ];
    }
}
