<?php


namespace Doyo\Behat\Coverage\Bridge\CodeCoverage;


class TestCase
{
    const RESULT_UNKNOWN = -1;
    const RESULT_PASSED = 0;
    const RESULT_SKIPPED = 1;
    const RESULT_ERROR = 5;
    const RESULT_FAILED = 3;

    private $name;

    private $result;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return TestCase
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }
}
