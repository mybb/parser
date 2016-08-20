<?php
/**
 * Smilie repository using Eloquent to retrieve smilies from the database.
 *
 * @author  MyBB Group
 * @version 2.0.0
 * @package mybb/parser
 * @license http://www.mybb.com/licenses/bsd3 BSD-3
 */

namespace MyBB\Parser\Database\Repositories\Eloquent;

use Illuminate\Support\Collection;
use MyBB\Parser\Database\Models\Smiley;
use MyBB\Parser\Database\Repositories\SmileyRepositoryInterface;

class SmileyRepository implements SmileyRepositoryInterface
{
    /**
     * @var Smiley $model
     */
    protected $model;

    /**
     * @param Smiley $model The smiley model to use.
     */
    public function __construct(Smiley $model)
    {
        $this->model = $model;
    }

    /**
     * Get all of the defined smileys.
     *
     * Smileys should be returned as an array of ['find' => 'replace'].
     *
     * @return array
     */
    public function getAllForParsing(): array
    {
        return $this->model->newQuery()->orderBy('disporder')->lists('image', 'find');
    }

    /**
     * Get all defined smileys.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->model->newQuery()->orderBy('disporder')->all();
    }
}
