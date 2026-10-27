<?php

namespace App\Filament\Supervisor\Resources\Students\Pages;

use App\Filament\Supervisor\Resources\Students\StudentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;
}
