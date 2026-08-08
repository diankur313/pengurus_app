<?php

namespace App\Filament\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EditProfile extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
    use HasPageShield;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static string $view = 'filament.pages.edit-profile';
    protected static ?string $title = 'Edit Profil';
    protected static ?string $navigationLabel = 'Edit Profil';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $profileData = [];
    public ?array $passwordData = [];

    public function mount(): void
    {
        $user = Auth::user();

        $this->profileForm->fill([
            'name' => $user->name,
            'whatsapp' => $user->whatsapp,
            'goldar' => $user->goldar,
            'photo' => $user->photo,
        ]);

        $this->passwordForm->fill();
    }

    protected function getForms(): array
    {
        return [
            'profileForm',
            'passwordForm',
        ];
    }

    public function profileForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Profil')
                    ->description('Perbarui informasi profil Anda.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        Forms\Components\FileUpload::make('photo')
                            ->label('Foto Profil')
                            ->image()
                            ->avatar()
                            ->disk('profile_private')
                            ->directory('')
                            ->imageEditor()
                            ->circleCropper()
                            ->maxSize(10240) // 10MB max upload
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-user'),

                        Forms\Components\TextInput::make('whatsapp')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->maxLength(20)
                            ->prefixIcon('heroicon-o-phone')
                            ->placeholder('08xxxxxxxxxx'),

                        Forms\Components\Select::make('goldar')
                            ->label('Golongan Darah')
                            ->options([
                                'A' => 'A',
                                'B' => 'B',
                                'AB' => 'AB',
                                'O' => 'O',
                                'A+' => 'A+',
                                'A-' => 'A-',
                                'B+' => 'B+',
                                'B-' => 'B-',
                                'AB+' => 'AB+',
                                'AB-' => 'AB-',
                                'O+' => 'O+',
                                'O-' => 'O-',
                            ])
                            ->searchable()
                            ->prefixIcon('heroicon-o-heart')
                            ->placeholder('Pilih golongan darah'),
                    ])
                    ->columns(2),
            ])
            ->statePath('profileData');
    }

    public function passwordForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Ubah Password')
                    ->description('Pastikan password Anda cukup kuat untuk keamanan akun.')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Password Baru')
                            ->password()
                            ->revealable()
                            ->rule(Password::min(8)->mixedCase()->numbers()->letters())
                            ->reactive()
                            ->dehydrated(fn ($state) => filled($state))
                            ->helperText('Minimal 8 karakter, kombinasi huruf besar, huruf kecil, dan angka.')
                            ->prefixIcon('heroicon-o-key'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->same('password')
                            ->requiredWith('password')
                            ->dehydrated(false)
                            ->prefixIcon('heroicon-o-key'),
                    ])
                    ->columns(1),
            ])
            ->statePath('passwordData');
    }

    public function updateProfile(): void
    {
        sleep(1); // Simulate processing time for UX
        $profileData = $this->profileForm->getState();
        $user = Auth::user();

        $photoName = $user->photo;

        // Handle photo update, compression, and renaming
        if (isset($profileData['photo']) && $profileData['photo'] !== $user->photo) {
            $photoName = $this->processAndMovePhoto($profileData['photo']);
        }

        $user->update([
            'name' => $profileData['name'],
            'whatsapp' => $profileData['whatsapp'],
            'goldar' => $profileData['goldar'],
            'photo' => $photoName,
        ]);

        Notification::make()
            ->title('Profil berhasil diperbarui!')
            ->success()
            ->send();
    }

    public function updatePassword(): void
    {
        sleep(1); // Simulate processing time for UX
        $passwordData = $this->passwordForm->getState();

        if (empty($passwordData['password'])) {
            Notification::make()
                ->title('Password tidak diisi.')
                ->warning()
                ->send();
            return;
        }

        Auth::user()->update([
            'password' => Hash::make($passwordData['password']),
        ]);

        $this->passwordForm->fill();

        Notification::make()
            ->title('Password berhasil diubah!')
            ->success()
            ->send();
    }

    protected function processAndMovePhoto(string $tempFilename): string
    {
        $disk = Storage::disk('profile_private');
        $filePath = $disk->path($tempFilename);

        if (!file_exists($filePath)) {
            return $tempFilename;
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $randomName = Str::random(12) . '.' . $extension;
        $newPath = $disk->path($randomName);

        try {
            $manager = new ImageManager(Driver::class);
            $image = $manager->decode($filePath);

            // Check file size (3MB = 3,145,728 bytes)
            $fileSize = filesize($filePath);
            
            if ($fileSize > 3 * 1024 * 1024) {
                // Compress 60% (quality)
                if (strtolower($extension) === 'png') {
                    // Convert PNG to JPG for better compression
                    $randomName = Str::random(12) . '.jpg';
                    $newPath = $disk->path($randomName);
                    $image->toJpeg(60)->save($newPath);
                } else {
                    $image->save($newPath, quality: 60);
                }
            } else {
                // Just rename/move if under 3MB
                rename($filePath, $newPath);
            }

            // Remove temp file if it's different from the new file
            if ($filePath !== $newPath && file_exists($filePath)) {
                unlink($filePath);
            }

            return $randomName;
        } catch (\Exception $e) {
            \Log::error('Failed to process profile photo: ' . $e->getMessage());
            return $tempFilename;
        }
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
