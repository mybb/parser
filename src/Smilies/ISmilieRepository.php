<?php

namespace MyBB\Parser\Smilies;

interface ISmilieRepository
{
	/**
	 * @return array
	 */
	public function getParsableSmilies();
}
