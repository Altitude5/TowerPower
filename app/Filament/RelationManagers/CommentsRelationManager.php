<?php

namespace App\Filament\RelationManagers;

use App\Models\Comment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class CommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('text')
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('image_url')
                    ->image()
                    ->disk('public')
                    ->directory('comments')
                    ->visibility('public')
                    ->label('Attachment'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('text')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('author'))
            ->columns([
                TextColumn::make('author.name')
                    ->label('Author')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('text')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),
                ImageColumn::make('image_url')
                    ->label('Image')
                    ->disk('public')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    })
                    ->visible(fn () => Gate::allows('create', [Comment::class, $this->getOwnerRecord()])),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn (Comment $record) => Gate::allows('update', $record)),
                DeleteAction::make()
                    ->visible(fn (Comment $record) => Gate::allows('delete', $record)),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->isSuperUser()),
                ]),
            ]);
    }
}
