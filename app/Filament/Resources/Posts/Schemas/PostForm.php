<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Resources\Projects\Schemas\ProjectForm;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')->live(onBlur: true)->afterStateUpdated(function ($state, \Filament\Schemas\Components\Utilities\Get $get, \Filament\Schemas\Components\Utilities\Set $set) { if (blank($get('slug'))) $set('slug', \Illuminate\Support\Str::slug($state)); })->label('Judul kabar riset')
                    ->required(),
                TextInput::make('slug')->helperText('Terisi dari judul atau nama. Gunakan huruf kecil dan tanda hubung, misalnya smart-water-pump.')->unique(ignoreRecord: true)->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')->label('Alamat halaman berita')
                    ->required(),
                TextInput::make('excerpt')->label('Ringkasan'),
                Textarea::make('body')->label('Isi berita')
                    ->columnSpanFull(),
                FileUpload::make('thumbnail')->label('Foto utama')->disk('uploads')
		    ->image()
		    ->directory('posts')->imageResizeMode('contain')->imageResizeTargetWidth('1280')->imageResizeTargetHeight('1280')->imageResizeUpscale(false)
		    ->imageEditor(),
                FileUpload::make('images')
                    ->label('Gambar tambahan')
                    ->helperText('Bisa menambahkan hingga 12 foto. Foto utama tetap menjadi sampul berita.')
                    ->image()
                    ->disk('uploads')
                    ->directory('posts')->imageResizeMode('contain')->imageResizeTargetWidth('1280')->imageResizeTargetHeight('1280')->imageResizeUpscale(false)
                    ->multiple()
                    ->reorderable()
                    ->maxFiles(12)
                    ->maxSize(10240)
                    ->columnSpanFull(),
                Select::make('project_id')->label('Project terkait')
                    ->relationship('project', 'name')
                    ->searchable()->preload()
                    ->helperText('Pilih project yang sudah ada, atau tambahkan project baru di sini.')
                    ->createOptionForm(fn (Schema $schema): Schema => ProjectForm::configure($schema))
                    ->createOptionAction(fn (Action $action): Action => $action
                        ->label('Tambah project')->button()
                        ->modalHeading('Tambah project')
                        ->modalSubmitActionLabel('Simpan project')),
                Select::make('status')->label('Publikasi')->options(['draft'=>'Draf','published'=>'Terbit'])
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('published_at')->label('Tanggal terbit'),
            ]);
    }
}
