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

namespace Doyo\Behat\Coverage\Bridge;

/**
 * Aggregate.
 *
 * @author Anthon Pang <apang@softwaredevelopment.ca>
 */
class Aggregate
{
    /**
     * @var array
     */
    private $coverage = [];

    /**
     * Update aggregated coverage.
     *
     * @param string $class
     */
    public function update($class, array $counts)
    {
        if (!isset($this->coverage[$class])) {
            $this->coverage[$class] = $counts;

            return;
        }

        foreach ($counts as $line => $status) {
            if (!isset($this->coverage[$class][$line]) || $status > 0) {
                // converts "hits" to "status"
                $status = !$status ? -1 : ($status > 1 ? 1 : $status);

                $this->coverage[$class][$line] = $status;
            }
        }
    }

    /**
     * Get coverage.
     *
     * @return array
     */
    public function getCoverage()
    {
        return $this->coverage;
    }
}
