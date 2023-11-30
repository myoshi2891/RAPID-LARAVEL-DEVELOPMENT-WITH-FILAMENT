<?php

namespace App\Models;

use App\Models\Talk;
use App\Enums\Region;
use App\Models\Venue;
use App\Models\Speaker;
use Filament\Forms\Get;
use App\Models\Attendee;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Conference extends Model
{
    use HasFactory;

    protected $casts = [
        'id' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'region' => Region::class,
        'venue_id' => 'integer',
    ];

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(Speaker::class);
    }

    public function talks(): BelongsToMany
    {
        return $this->belongsToMany(Talk::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public static function getForm(): array
    {
        return [
        Tabs::make()
            ->columnSpanFull()
            ->tabs([
                Tab::make('Conference Details')
                ->schema([
                    TextInput::make('name')
                        ->columnSpanFull()
                        ->label('Conference Name')
                        ->default('My Conference')
                        ->required()
                        ->maxLength(60),
                    MarkdownEditor::make('description')
                        ->columnSpanFull()
                        ->required(),
                    DateTimePicker::make('start_date')
                        ->native(false)
                        ->required(),
                    DateTimePicker::make('end_date')
                        ->native(false)
                        ->required(),
                    Fieldset::make('Status')
                        ->columns(1)
                        ->schema([
                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->required(),
                    Toggle::make('is_published')
                        ->default(true),
                    ])
                ]),
                Tab::make('Locations')
                ->schema([
                    Select::make('region')
                        ->live()
                        ->enum(Region::class)
                        ->options(Region::class),
                    Select::make('venue_id')
                        ->searchable()
                        ->preload()
                        ->createOptionForm(Venue::getForm())
                        ->editOptionForm(Venue::getForm())
                        ->relationship('venue', 'name', modifyQueryUsing: function (Builder $query, Get $get) {                                       
                            return $query->where('region', $get('region'));
                        }),
                ]),
            ]),
            Actions::make([
                Action::make('star')
                ->label('Fill with Factory Data')
                ->icon('heroicon-m-star')
                ->visible(function (string $operation){
                    if($operation !== 'create'){
                        return false;
                    }
                    if(! app()->environment('local')){
                        return false;
                    }
                    return true;
                })
                ->action(function($livewire) {
                    $data = Conference::factory()->make()->toArray();
                    // unset($data['venue_id']);
                    $livewire->form->fill($data);
                })
            ]),
        // Section::make('Conference Details')
        // ->collapsible()
        // // ->aside()
        // ->description('Provide some basic information about the conference.')
        // ->icon('heroicon-o-information-circle')
        // ->columns(['md' => 2, 'lg' => 3]),

        // Section::make('Location')
        // ->columns(2),

        CheckboxList::make('speakers')
            ->columnSpanFull()
            ->columns(3)
            ->relationship('speakers', 'name')
            ->options(
                Speaker::all()->pluck('name', 'id')
            )
            ->required(),
        ];
    }
}