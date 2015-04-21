<?php

namespace MyBB\Parser\Badwords;

interface BadwordRepositoryInterface
{
	/**
	 * @return array
	 */
	public function getAllAsArray();
}
