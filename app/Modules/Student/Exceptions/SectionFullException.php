<?php

namespace App\Modules\Student\Exceptions;

use Exception;

class SectionFullException extends Exception
{
    public function __construct(int $sectionId)
    {
        parent::__construct("Section #{$sectionId} has reached its maximum capacity.");
    }
}
