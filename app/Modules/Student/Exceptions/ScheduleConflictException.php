<?php

namespace App\Modules\Student\Exceptions;

use Exception;

class ScheduleConflictException extends Exception
{
    protected array $conflicts;

    public function __construct(array $conflicts = [])
    {
        $this->conflicts = $conflicts;
        parent::__construct('The selected section conflicts with an existing enrollment schedule.');
    }

    public function getConflicts(): array
    {
        return $this->conflicts;
    }
}
