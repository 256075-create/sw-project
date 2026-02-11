<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Identity\Providers\IdentityServiceProvider::class,
    App\Modules\Academic\Providers\AcademicServiceProvider::class,
    App\Modules\Registration\Providers\RegistrationServiceProvider::class,
    App\Modules\Student\Providers\StudentServiceProvider::class,
    App\Providers\SearchServiceProvider::class,
];
