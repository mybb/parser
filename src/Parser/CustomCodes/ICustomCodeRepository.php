<?php

namespace MyBB\Parser\Parser\CustomCodes;

interface ICustomCodeRepository
{
    /**
     * @return array
     */
    public function getParsableCodes();
}