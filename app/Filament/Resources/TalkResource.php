<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\Talk;
use Filament\Tables;
use Filament\Forms\Form;
use App\Enums\TalkLength;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Tables\Columns\TextInputColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\TalkResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\TalkResource\RelationManagers;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('abstract')
                    ->required()
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('speaker_id')
                    ->relationship('speaker', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(function($action) {
                return $action->button()->label('Filters');
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->sortable()
                    // ->rules(['required', 'max:255'])
                    ->searchable()
                    ->description(function (Talk $record){
                        return Str::of($record->abstract)->limit(40);
                    }),
                ImageColumn::make('speaker.avatar')
                    ->label('Speaker Avatar')
                    ->circular()
                    ->defaultImageUrl(function ($record){
                        return 'https://ui-avatars.com/api/?background=0D8ABC&color=fff&name=' . urlencode($record->speaker->name);                
                    }),
                Tables\Columns\TextColumn::make('speaker.name')
                    ->sortable()
                    ->searchable(),
                ToggleColumn::make('new_talk'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(function ($state){
                        return $state->getColor();
                    }),
                IconColumn::make('length')
                    ->icon(function($state){
                        return match($state){
                            TalkLength::NORMAL => 'heroicon-o-megaphone',
                            TalkLength::LIGHTNING => 'heroicon-o-bolt',
                            TalkLength::KEYNOTE => 'heroicon-o-key',
                        };
                    })
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('new_talk'),
                Tables\Filters\SelectFilter::make('speaker')
                ->relationship('speaker', 'name')
                ->multiple()
                ->searchable()
                ->preload(),
                Tables\Filters\Filter::make('has_avatar')
                ->label('Show Only Speakers With Avatars')
                ->toggle()
                ->query(function($query){
                    return $query->whereHas('speaker', function (Builder $query){
                        $query->whereNotNull('avatar');
                    });
                })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTalks::route('/'),
            'create' => Pages\CreateTalk::route('/create'),
            'edit' => Pages\EditTalk::route('/{record}/edit'),
        ];
    }
}