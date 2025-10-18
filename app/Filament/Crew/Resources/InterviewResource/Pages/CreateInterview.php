<?php

namespace App\Filament\Crew\Resources\InterviewResource\Pages;

use App\Filament\Crew\Resources\InterviewResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\NamaKapal;
use Filament\Actions\StaticAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Support\Enums\MaxWidth;


class CreateInterview extends CreateRecord
{
    protected static string $resource = InterviewResource::class;
  
}
