<?php
/**
 * Interface for repositories used to get MyCodes.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories;

use Illuminate\Support\Collection;

interface CustomMyCodeRepositoryInterface
{
    /**
     * Get all of the custom MyCodes, in the form [find => replace].
     *
     * @return array
     */
    public function getParseableCodes();

    /**
     * Get all of the custom MyCodes.
     *
     * @return Collection
     */
    public function getAll();
}
