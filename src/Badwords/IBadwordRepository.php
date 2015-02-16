<?php

namespace MyBB\Parser\Badwords;

interface IBadwordRepository
{
    /**
     * @return array
     */
    public function getAllAsArray();
}