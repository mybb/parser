<?php

namespace MyBB\Parser\Parser\CustomCodes;

interface CustomCodeRepositoryInterface
{
	/**
	 * @return array
	 */
	public function getParsableCodes();
}
