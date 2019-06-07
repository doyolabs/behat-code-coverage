<?php


namespace spec\Doyo\Behat\Coverage\Listener;


use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use SebastianBergmann\CodeCoverage\Filter;

class AbstractSessionCoverageListener
{
    /**
     * @var SessionInterface
     */
    protected $session;

    protected $codeCoverageOptions;

    protected $filterOptions;

    public function __construct(SessionInterface $session, array $codeCoverageOptions, Filter $filter)
    {
        $whitelistedFiles              = $filter->getWhitelistedFiles();
        $filter                        = [];
        $filter['whitelistedFiles'] = $whitelistedFiles;

        $session->setCodeCoverageOptions($codeCoverageOptions);
        $session->setFilterOptions($filter);
        $session->save();

        $this->session = $session;
        $this->codeCoverageOptions = $codeCoverageOptions;
        $this->filterOptions = $filter;
    }
}
