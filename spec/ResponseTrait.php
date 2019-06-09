<?php


namespace spec\Doyo\Behat\Coverage;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Webmozart\Assert\Assert;

/**
 * Trait ResponseTrait
 */
trait ResponseTrait
{

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
            },
            'beAHttpResponse' => function($subject){
                Assert::isInstanceOf($subject, Response::class);

                return true;
            },
            'beASerializedObject' => function($subject, $expected){
                /* @var \Symfony\Component\HttpFoundation\Response $subject */
                $serialized = unserialize($subject->getContent());
                $class = $expected;
                if(!is_string($class)){
                    $class = get_class($class);
                }
                Assert::isInstanceOf($serialized, $class);

                return true;
            }
        ];
    }
}
