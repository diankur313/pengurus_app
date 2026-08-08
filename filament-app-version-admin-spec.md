# Spec: Menu "App Version" di Filament — Sumber Data buat Force Update

## Tujuan
Menu baru di folder pendidikan, tandai satu versi sebagai **paten** (wajib), dan atur pesan custom yang bakal muncul di app kalau user belum update ke versi itu. Nanti jangan lupa lo daftarin ke fillament shield menu ini kalo udah selesai

Ini pasangan dari spec pertama (`android-version-check-spec.md`) yang konsumsi data ini lewat API.

## 1. Migration

```php
Schema::create('app_versions', function (Blueprint $table) {
    $table->id();
    $table->string('platform')->default('android'); // android / ios
    $table->string('version_name'); // "2.0.0"
    $table->unsignedInteger('version_code'); // harus sama dengan versionCode di build.gradle
    $table->boolean('is_mandatory')->default(false); // "paten" / wajib
    $table->text('custom_message')->nullable();
    $table->string('download_url')->nullable();
    $table->text('changelog')->nullable();
    $table->timestamp('released_at')->nullable();
    $table->timestamps();

    $table->unique(['platform', 'version_code']);
});
```

## 2. Model

```php
class AppVersion extends Model
{
    protected $fillable = [
        'platform', 'version_name', 'version_code',
        'is_mandatory', 'custom_message', 'download_url',
        'changelog', 'released_at',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'released_at' => 'datetime',
    ];
}
```

## 3. Filament Resource

Generate dulu:
```bash
php artisan make:filament-resource AppVersion --generate
```

Lalu sesuaikan form & table-nya:

```php
class AppVersionResource extends Resource
{
    protected static ?string $model = AppVersion::class;
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'App Versions';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('platform')
                ->options(['android' => 'Android', 'ios' => 'iOS'])
                ->required(),

            TextInput::make('version_name')
                ->required()
                ->helperText('Contoh: 2.0.0'),

            TextInput::make('version_code')
                ->numeric()
                ->required()
                ->helperText('Harus sama dengan versionCode di build.gradle app'),

            Toggle::make('is_mandatory')
                ->label('Jadikan versi paten (wajib)')
                ->helperText('Kalau aktif, semua user dengan versi di bawah ini akan diblok sampai update.')
                ->live(),

            Textarea::make('custom_message')
                ->label('Pesan yang muncul saat user diblok')
                ->visible(fn ($get) => $get('is_mandatory'))
                ->required(fn ($get) => $get('is_mandatory')),

            TextInput::make('download_url')
                ->url()
                ->helperText('Link Play Store'),

            Textarea::make('changelog'),

            DateTimePicker::make('released_at'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('platform'),
                TextColumn::make('version_name'),
                TextColumn::make('version_code')->sortable(),
                IconColumn::make('is_mandatory')->boolean()->label('Paten'),
                TextColumn::make('released_at')->dateTime(),
            ])
            ->defaultSort('version_code', 'desc');
    }
}
```

Catatan: kalau ada lebih dari satu row `is_mandatory = true`, endpoint di bawah otomatis ambil yang `version_code` paling tinggi — jadi aman, lo gak perlu manual matiin toggle di versi lama tiap kali rilis versi paten baru (tapi lebih rapi kalau dimatiin biar gak bingung pas liat listnya).

## 4. Endpoint API (dikonsumsi app Android)

Route (`routes/api.php`):
```php
Route::get('/app-version', [AppVersionController::class, 'check']);
```

Controller:
```php
class AppVersionController extends Controller
{
    public function check(Request $request)
    {
        $platform = $request->query('platform', 'android');

        $mandatory = AppVersion::where('platform', $platform)
            ->where('is_mandatory', true)
            ->orderByDesc('version_code')
            ->first();

        return response()->json([
            'platform' => $platform,
            'mandatory_version_code' => $mandatory?->version_code,
            'mandatory_version_name' => $mandatory?->version_name,
            'custom_message' => $mandatory?->custom_message,
            'download_url' => $mandatory?->download_url,
        ]);
    }
}
```

Response ini persis yang dikonsumsi `VersionApi` di spec Android — jadi kedua sisi udah nyambung tanpa perlu adaptasi tambahan.

## Alur singkat
1. Lo rilis versi baru app, misal `versionCode` 20.
2. Kalau versi ini wajib dipakai semua user, masuk ke menu App Versions → tambah row baru, isi `version_code = 20`, toggle **"Jadikan versi paten"**, isi pesan custom-nya.
3. App yang masih di `versionCode` di bawah 20 otomatis keblok pas dibuka, nampilin pesan yang lo tulis di step 2.
4. Kalau belum ada row yang ditandai paten, semua versi app boleh jalan normal.
