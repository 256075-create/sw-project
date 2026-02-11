<?php

namespace App\Modules\Student\Exceptions;

use Exception;

class DuplicateEnrollmentException extends Exception
{
    public function __construct(int $studentId, int $sectionId)
    {
        parent::__construct("Student #{$studentId} is already enrolled in section #{$sectionId}.");
    }
}
