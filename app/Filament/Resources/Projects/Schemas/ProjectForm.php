<?php

namespace App\Filament\Resources\Projects\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->live(onBlur: true)->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) { if (blank($get('slug'))) $set('slug', \Illuminate\Support\Str::slug($state)); })->label('Nama project')
                    ->required(),
                TextInput::make('slug')->helperText('Otomatis dari nama. Contoh: smart-water-pump.')->unique(ignoreRecord: true)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->label('Alamat halaman project')
                    ->required(),
                TextInput::make('summary')->required()->label('Ringkasan')->columnSpanFull(),
                Textarea::make('description')->required()->label('Tentang project')
                    ->rows(3)->columnSpanFull(),
                FileUpload::make('thumbnail')->required()->label('Foto utama')
        		    ->image()
        		    ->disk('uploads')
        		    ->directory('projects')->imageResizeMode('contain')->imageResizeTargetWidth('1280')->imageResizeTargetHeight('1280')->imageResizeUpscale(false)
        		    ->imageEditor()->imagePreviewHeight('110'),
                FileUpload::make('images')->required()
                    ->label('Foto pendukung')
                    ->image()
                    ->disk('uploads')
                    ->directory('projects')->imageResizeMode('contain')->imageResizeTargetWidth('1280')->imageResizeTargetHeight('1280')->imageResizeUpscale(false)
                    ->multiple()->minFiles(1)
                    ->reorderable()
                    ->maxFiles(12)
                    ->maxSize(10240)->imagePreviewHeight('90'),
                Textarea::make('specs')->required()->label('Spesifikasi')
                    ->rows(3)->columnSpanFull(),
                TextInput::make('category')->required()->label('Kategori'),
                TextInput::make('dashboard_url')->required()->label('Tautan halaman monitoring')
                    ->url(),
                Select::make('status')->label('Publikasi')->options(['draft'=>'Draf','published'=>'Terbit'])
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('published_at')->required()->label('Tanggal terbit'),
            ]);
    }
}
