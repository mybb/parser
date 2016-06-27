<?php
/**
 * Interface for repositories used to get bad words.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories;

use Illuminate\Support\Collection;

interface BadWordRepositoryInterface
{
    /**
     * Get all bad words.
     *
     * @return Collection
     */
    public function getAll();

    /**
     * Get all of the defined bad words as an array ready for parsing.
     *
     * Bad words should be returned in the form [find => replace].
     *
     * @return array
     */
    public function getAllForParsing();
}
