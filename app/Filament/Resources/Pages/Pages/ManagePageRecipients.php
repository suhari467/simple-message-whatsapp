<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Imports\RecipientImport;
use App\Models\Recipient;
use BackedEnum;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class ManagePageRecipients extends ManageRelatedRecords
{
    protected static string $resource = PageResource::class;

    protected static string $relationship = 'recipients';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function getNavigationLabel(): string
    {
        return 'Penerima';
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('index')
                    ->label('No')
                    ->rowIndex(),
                TextColumn::make('name')
                    ->label('Nama Penerima')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->placeholder('-')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->label('Nomor Telepon')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->form([
                        TextInput::make('name')
                            ->label('Nama Penerima')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3),
                        TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(50),
                    ]),
                Action::make('import')
                    ->label('Import CSV/Excel')
                    ->icon(Heroicon::OutlinedArrowUpTray)
                    ->color('info')
                    ->form([
                        Placeholder::make('template_info')
                            ->label('Unduh Template')
                            ->content(new HtmlString('<a href="'.asset('assets/docs/template_penerima.xlsx').'" class="text-primary-600 dark:text-primary-400 underline font-medium" download>Download Template Penerima (XLSX)</a>')),
                        FileUpload::make('file')
                            ->label('File Excel/CSV')
                            ->required()
                            ->disk('local')
                            ->directory('temp-imports')
                            ->acceptedFileTypes([
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'text/csv',
                                'text/plain',
                            ]),
                    ])
                    ->action(function (array $data) {
                        $filePath = Storage::disk('local')->path($data['file']);

                        Excel::import(new RecipientImport($this->getOwnerRecord()->id), $filePath);

                        Storage::disk('local')->delete($data['file']);

                        Notification::make()
                            ->title('Data berhasil diimport')
                            ->success()
                            ->send();
                    }),
            ])
            ->recordActions([
                Action::make('copy_link')
                    ->label('Copy Link')
                    ->icon(Heroicon::OutlinedClipboard)
                    ->color('info')
                    ->action(function (Recipient $record) {
                        $url = url('/'.$record->page->slug.'?to='.urlencode($record->name));
                        $this->js("window.navigator.clipboard.writeText('{$url}');");

                        Notification::make()
                            ->title('Tautan berhasil disalin')
                            ->success()
                            ->send();
                    }),
                Action::make('send_whatsapp')
                    ->label('Send WhatsApp')
                    ->icon(Heroicon::OutlinedShare)
                    ->color('success')
                    ->action(function (Recipient $record) {
                        $page = $record->page;

                        $weddingDate = $page->wedding_date;
                        $akadTime = $page->akad_time;
                        $hariTanggal = '';
                        $jam = '';
                        try {
                            $hariTanggal = Carbon::parse($weddingDate)->locale('id')->translatedFormat('l, d F Y');
                            $jam = Carbon::parse($akadTime)->format('H:i');
                        } catch (\Exception $e) {
                            $hariTanggal = $weddingDate;
                            $jam = $akadTime;
                        }

                        $slugLink = url('/'.$page->slug);

                        $template = "The Wedding Of {$page->name}

Kepada Yth. 
Bapak/Ibu/Saudara/i
{$record->name}

--
Tanpa mengurangi rasa hormat, perkenankan kami mengundang Bapak/Ibu/Saudara/i, teman sekaligus sahabat, untuk menghadiri acara pernikahan kami:

{$page->bride_name}
Putri dari {$page->bride_parents}
&
{$page->groom_name}
Putra dari {$page->groom_parents}

\u{1F4C5} {$hariTanggal}
\u{1F550} {$jam} WIB 
\u{1F4CD} {$page->akad_location}

Silahkan melakukan konfirmasi kehadiran dengan mengisi RSVP yang ada pada link dibawah ini:
{$slugLink}?to=".rawurlencode($record->name)."

Merupakan suatu kebahagiaan bagi kami apabila Bapak/Ibu berkenan untuk hadir dan memberikan doa restu.

Hormat Kami, yang berbahagia.
{$page->name}";

                        $jsonTemplate = json_encode($template);

                        if ($record->phone_number) {
                            $phone = preg_replace('/[^0-9]/', '', $record->phone_number);
                            if (str_starts_with($phone, '0')) {
                                $phone = '62'.substr($phone, 1);
                            }
                            $this->js("
                                let template = {$jsonTemplate};
                                let url = 'https://wa.me/{$phone}?text=' + encodeURIComponent(template);
                                window.open(url, '_blank');
                            ");
                        } else {
                            $this->js("window.navigator.clipboard.writeText({$jsonTemplate});");

                            Notification::make()
                                ->title('Nomor tidak ada. Template undangan disalin ke clipboard.')
                                ->info()
                                ->send();
                        }
                    }),
                EditAction::make()
                    ->form([
                        TextInput::make('name')
                            ->label('Nama Penerima')
                            ->required()
                            ->maxLength(255),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3),
                        TextInput::make('phone_number')
                            ->label('Nomor Telepon')
                            ->tel()
                            ->maxLength(50),
                    ]),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
