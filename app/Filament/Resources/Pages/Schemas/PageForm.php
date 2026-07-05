<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Dasar Grup')
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Grup (Mempelai)')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->readOnly(),
                        FileUpload::make('logo')
                            ->label('Foto Profil Grup / Logo')
                            ->image()
                            ->disk('public')
                            ->directory('logos'),
                        Textarea::make('description')
                            ->label('Deskripsi Singkat Undangan')
                            ->columnSpanFull(),
                        MarkdownEditor::make('content')
                            ->label('Konten Papan Informasi / Pesan Penyambut (Markdown)')
                            ->columnSpanFull(),
                        FileUpload::make('background_music')
                            ->label('Lagu Latar Belakang (MP3)')
                            ->disk('public')
                            ->directory('music')
                            ->rules(['extensions:mp3'])
                            ->maxSize(6144)
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Detail Mempelai & Acara')
                    ->schema([
                        TextInput::make('bride_name')
                            ->label('Nama Pengantin Wanita')
                            ->required(),
                        TextInput::make('bride_parents')
                            ->label('Nama Orang Tua Pengantin Wanita (Bapak & Ibu)')
                            ->placeholder('Contoh: Bapak Suhadiya & Ibu Heri'),
                        FileUpload::make('bride_image')
                            ->label('Foto Pengantin Wanita')
                            ->image()
                            ->disk('public')
                            ->directory('brides'),
                        TextInput::make('groom_name')
                            ->label('Nama Pengantin Pria')
                            ->required(),
                        TextInput::make('groom_parents')
                            ->label('Nama Orang Tua Pengantin Pria (Bapak & Ibu)')
                            ->placeholder('Contoh: Bapak Sudirman (Alm.) & Ibu Lilis'),
                        FileUpload::make('groom_image')
                            ->label('Foto Pengantin Pria')
                            ->image()
                            ->disk('public')
                            ->directory('grooms'),
                        DatePicker::make('wedding_date')
                            ->label('Tanggal Pernikahan')
                            ->required(),
                        TextInput::make('akad_time')
                            ->label('Waktu Akad Nikah')
                            ->placeholder('Contoh: 09:00 - 11:00 WIB')
                            ->required(),
                        TextInput::make('akad_location')
                            ->label('Tempat/Lokasi Akad Nikah')
                            ->required(),
                        TextInput::make('resepsi_time')
                            ->label('Waktu Resepsi')
                            ->placeholder('Contoh: 12:00 - Selesai')
                            ->required(),
                        TextInput::make('resepsi_location')
                            ->label('Tempat/Lokasi Resepsi')
                            ->required(),
                        TextInput::make('google_maps_url')
                            ->label('Link Google Maps Lokasi')
                            ->url()
                            ->columnSpanFull(),
                    ])->columns(3),

                Section::make('Media & Cerita Perjalanan (Timeline)')
                    ->schema([
                        Repeater::make('galleries')
                            ->relationship('galleries')
                            ->label('Galeri Foto Prewedding')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->label('Foto')
                                    ->image()
                                    ->disk('public')
                                    ->directory('prewedding')
                                    ->required(),
                            ])
                            ->grid(3)
                            ->columnSpanFull(),

                        Repeater::make('stories')
                            ->relationship('stories')
                            ->label('Timeline Perjalanan Cinta (Love Story)')
                            ->schema([
                                TextInput::make('title')
                                    ->label('Judul Momen')
                                    ->placeholder('Contoh: Pertama Bertemu')
                                    ->required(),
                                TextInput::make('date_or_year')
                                    ->label('Waktu / Tahun')
                                    ->placeholder('Contoh: Tahun 2020')
                                    ->required(),
                                Textarea::make('description')
                                    ->label('Cerita Momen Ini')
                                    ->required(),
                                FileUpload::make('image_path')
                                    ->label('Foto Momen (Opsional)')
                                    ->image()
                                    ->disk('public')
                                    ->directory('stories'),
                                TextInput::make('sort_order')
                                    ->label('Urutan Tampil')
                                    ->numeric()
                                    ->default(0),
                            ])
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Hadiah Digital / Donasi')
                    ->schema([
                        Repeater::make('donations')
                            ->relationship('donations')
                            ->label('Daftar Rekening / E-Wallet Donasi')
                            ->schema([
                                TextInput::make('bank_name')
                                    ->label('Nama Bank / E-Wallet')
                                    ->placeholder('Contoh: Bank BCA, Mandiri, Gope, OVO')
                                    ->required(),
                                TextInput::make('account_name')
                                    ->label('Atas Nama')
                                    ->placeholder('Contoh: Zuhriyani Salma')
                                    ->required(),
                                TextInput::make('account_number')
                                    ->label('Nomor Rekening / Nomor E-Wallet')
                                    ->placeholder('Contoh: 1234567890')
                                    ->required(),
                            ])
                            ->grid(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
