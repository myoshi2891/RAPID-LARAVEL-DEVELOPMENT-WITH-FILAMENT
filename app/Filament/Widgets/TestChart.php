<?php

namespace App\Filament\Widgets;

use Filament\Actions\Action;
use Filament\Widgets\Widget;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Actions\Contracts\HasActions;
use App\Filament\Resources\AttendeeResource;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Actions\Concerns\InteractsWithActions;

class TestChart extends Widget implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.test-chart';

    public function callNotification(): Action
    {
        return Action::make('callNotification')
        ->button()
        ->color('warning')
        ->label('Send a notification')
        ->action(function(){
            Notification::make()->warning()->title('You sent a notification')
            ->body('This is a test')
            ->persistent()
            ->actions([
                \Filament\Notifications\Actions\Action::make('goToAttendees')
                ->button()
                ->color('primary')
                ->url(AttendeeResource::getUrl('edit', ['record' => 1])),
                \Filament\Notifications\Actions\Action::make('undo')
                ->link()
                ->color('gray')
                ->url(AttendeeResource::getUrl('index')),
            ])
            // ->duration(500)
            ->send();
        });
    }
}