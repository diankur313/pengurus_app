# PPAB Finance Report - Excel Export Implementation Plan

## Context Analysis

Analyzed existing report system at `https://app.yiscalazhar.web.id/ppab-finance-report` in [`/www/wwwroot/app.yiscalazhar.web.id/ppab/app/Http/Controllers/ppab/ppabcontroller.php`](../app.yiscalazhar.web.id/ppab/app/Http/Controllers/ppab/ppabcontroller.php:405)

Current project has modal buttons created in [`resources/views/filament/modals/ppab-payment-report.blade.php`](../resources/views/filament/modals/ppab-payment-report.blade.php:1) but buttons are not wired yet.

## Current System Analysis (Old Project)

### Auth Logic
```php
if (Auth::user()->level == 'internal') {
    // Internal view - simplified report
} else {
    // Panitia view - detailed report with fees
}
```

### Report Types

#### Internal Report (Simplified)
**Columns:**
- No
- Name
- Channel (VA/QRIS/Indomaret)
- Bank Name
- Amount
- Remain (sisa pelunasan)

**Styling:**
- Yellow background for partial payment (DP)
- Gray background for full payment

#### Panitia Report (Detailed)
**Columns:**
- No
- Name
- Channel
- Bank Name
- Amount
- VAT (fee_pg)
- Fee Sysdev (fee_sysdev)
- Amount After (withdrawable)
- Remain

**Summary Rows:**
- Total Amount
- Total VAT
- Total Fee Sysdev
- Total Withdrawable
- Total Remain
- Grand Total

### Data Source
Query from [`ppab_transactions_xendit`](../app2.yiscalazhar.web.id/app/Models/PpabPayment.php:11) table:
- Filter: `status = 'PAID'`
- Filter: `id_session = user selected session` (with dropdown selection)
- Relations: member data via `id_member`

**⚠️ USER REQUEST:** Add session selector to modal - users should be able to choose which session to generate report for, not just latest session.

## Proposed Architecture

### 1. Package Installation

Install Excel export package:
```bash
composer require maatwebsite/excel
```

Filament already uses this internally, but explicit dependency needed.

### 2. Export Classes Structure

Create two export classes using Laravel Excel:

```
app/Exports/
├── PpabPaymentInternalExport.php
└── PpabPaymentPanitiaExport.php
```

### 3. Controller/Action Structure

Create dedicated controller for report generation:

```
app/Http/Controllers/PpabFinanceReportController.php
```

**Methods:**
- `exportInternal()` - generate internal Excel
- `exportPanitia()` - generate panitia Excel

### 4. Route Definition

Add routes in [`routes/web.php`](../routes/web.php):
```php
Route::middleware(['auth'])->group(function () {
    Route::get('/ppab-finance-report/internal', [PpabFinanceReportController::class, 'exportInternal'])
        ->name('ppab.finance.internal');
    
    Route::get('/ppab-finance-report/panitia', [PpabFinanceReportController::class, 'exportPanitia'])
        ->name('ppab.finance.panitia');
});
```

### 5. Modal Enhancement - Session Selector

Update [`resources/views/filament/modals/ppab-payment-report.blade.php`](../resources/views/filament/modals/ppab-payment-report.blade.php:1):

**New Structure:**
1. Session dropdown at top
2. Two report type buttons below

```blade
<div class="space-y-4" x-data="{ selectedSession: @js(\Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_session')->latest()->value('uuid')) }">
    <!-- Session Selector -->
    <div class="mb-6">
        <label class="block text-sm font-medium mb-2">Pilih Sesi PPAB</label>
        <select
            x-model="selectedSession"
            class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
        >
            @foreach(\Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_session')->orderBy('created_at', 'desc')->get() as $session)
                <option value="{{ $session->uuid }}">
                    {{ $session->name }} - {{ date('d M Y', strtotime($session->created_at)) }}
                    @if(\Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_transactions_xendit')->where('id_session', $session->uuid)->where('status', 'PAID')->count() > 0)
                        ({{ \Illuminate\Support\Facades\DB::connection('ppab')->table('ppab_transactions_xendit')->where('id_session', $session->uuid)->where('status', 'PAID')->count() }} pembayaran)
                    @endif
                </option>
            @endforeach
        </select>
    </div>

    <!-- Report Type Buttons -->
    <div class="grid grid-cols-2 gap-4">
        <x-filament::button
            type="button"
            color="success"
            icon="heroicon-o-document-chart-bar"
            size="lg"
            class="h-24"
            tag="a"
            x-bind:href="`{{ route('ppab.finance.internal') }}?session=${selectedSession}`"
            target="_blank"
        >
            <div class="flex flex-col items-center">
                <span class="text-lg font-semibold">Internal</span>
                <span class="text-xs opacity-75">Report untuk internal</span>
            </div>
        </x-filament::button>

        <x-filament::button
            type="button"
            color="primary"
            icon="heroicon-o-user-group"
            size="lg"
            class="h-24"
            tag="a"
            x-bind:href="`{{ route('ppab.finance.panitia') }}?session=${selectedSession}`"
            target="_blank"
        >
            <div class="flex flex-col items-center">
                <span class="text-lg font-semibold">Panitia</span>
                <span class="text-xs opacity-75">Report untuk panitia</span>
            </div>
        </x-filament::button>
    </div>
</div>
```

**Features:**
- Alpine.js reactive session selection
- Default to latest session
- Shows session name, date, and payment count
- Buttons dynamically update href with selected session

## Implementation Details

### Internal Export Class

**File:** `app/Exports/PpabPaymentInternalExport.php`

**Features:**
- Simple 6-column layout
- Row styling (yellow for DP, white for full payment)
- Summary row with totals
- Auto-width columns
- Currency formatting for amount columns

**Columns:**
1. No
2. Nama
3. Channel
4. Bank
5. Amount (Rupiah format)
6. Sisa Pelunasan (Rupiah format)

**Footer:**
- Total Amount
- Total Remain
- Grand Total

### Panitia Export Class

**File:** `app/Exports/PpabPaymentPanitiaExport.php`

**Features:**
- Detailed 9-column layout
- All financial breakdown visible
- Multiple summary rows
- Currency formatting
- Auto-width columns

**Columns:**
1. No
2. Nama
3. Channel
4. Bank
5. Amount (Rupiah)
6. VAT/Fee PG (Rupiah)
7. Fee Sysdev (Rupiah)
8. Amount After/Withdrawable (Rupiah)
9. Sisa Pelunasan (Rupiah)

**Footer:**
- Total Amount
- Total VAT
- Total Fee Sysdev
- Total Withdrawable
- Total Remain
- Grand Total

### Controller Logic

**File:** `app/Http/Controllers/PpabFinanceReportController.php`

```php
class PpabFinanceReportController extends Controller
{
    public function exportInternal(Request $request)
    {
        $sessionId = $this->resolveSessionId($request);
        $sessionName = $this->getSessionName($sessionId);

        return Excel::download(
            new PpabPaymentInternalExport($sessionId),
            'PPAB_Payment_Report_Internal_' . $sessionName . '_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportPanitia(Request $request)
    {
        $sessionId = $this->resolveSessionId($request);
        $sessionName = $this->getSessionName($sessionId);

        return Excel::download(
            new PpabPaymentPanitiaExport($sessionId),
            'PPAB_Payment_Report_Panitia_' . $sessionName . '_' . date('Ymd_His') . '.xlsx'
        );
    }

    /**
     * Resolve session ID: use ?session= param if provided, else latest
     */
    private function resolveSessionId(Request $request): string
    {
        if ($request->has('session')) {
            // Validate that session UUID exists
            $exists = DB::connection('ppab')
                ->table('ppab_session')
                ->where('uuid', $request->session)
                ->exists();
            
            if ($exists) {
                return $request->session;
            }
        }

        return DB::connection('ppab')
            ->table('ppab_session')
            ->latest()
            ->value('uuid');
    }

    private function getSessionName(string $sessionId): string
    {
        return DB::connection('ppab')
            ->table('ppab_session')
            ->where('uuid', $sessionId)
            ->value('name') ?? 'Unknown';
    }
}
```

### Data Query Logic

Both exports will use:

```php
$payments = PpabPayment::where('id_session', $sessionId)
    ->where('status', 'PAID')
    ->with('member')
    ->get();

// Calculate remain per payment
foreach ($payments as $payment) {
    $fullPrice = $this->getSessionPrice($payment->id_session);
    $remain = ($payment->payment_type === 'dp') 
        ? ($fullPrice - $payment->amount) 
        : 0;
}

// Totals
$totalAmount = $payments->sum('amount');
$totalVat = $payments->sum('fee_pg');
$totalSysdev = $payments->sum('fee_sysdev');
$totalWithdrawable = $payments->sum('withdrawable');
$totalRemain = $this->calculateTotalRemain($payments);
```

### Excel Styling

Use `maatwebsite/excel` features:

**Headers:**
- Bold text
- Background color (#f0f0f0)
- Border all sides
- Center alignment

**Data Rows (Internal):**
- Yellow background (#FFFF00) for DP payments
- White for full payments
- Right-align for amounts
- Border all sides

**Currency Format:**
```php
NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
// Or custom: 'Rp #,##0'
```

**Auto Width:**
```php
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}
```

## Database Fields Reference

From [`app/Models/PpabPayment.php`](../app2.yiscalazhar.web.id/app/Models/PpabPayment.php:11):
- Table: `ppab_transactions_xendit`
- Connection: `ppab`

**Relevant Fields:**
- `id_member` - FK to member
- `id_session` - FK to session
- `status` - payment status
- `amount` - total paid amount
- `method` - payment channel (va/qris/retail)
- `bank_name` - bank name for VA
- `payment_type` - 'dp' or 'full'
- `fee_pg` - payment gateway fee (VAT)
- `fee_sysdev` - system development fee
- `withdrawable` - amount after fees

## Security Considerations

1. **No Auth Level Check** - Both reports accessible by authenticated users
   - Old system checked `Auth::user()->level`
   - New system: buttons visible to all, let admin configure Filament permissions if needed

2. **Rate Limiting** - Add throttle middleware:
```php
Route::middleware(['auth', 'throttle:10,1'])->group(function () {
    // report routes
});
```

3. **Input Validation** - No user input, uses latest session automatically

## Testing Checklist

- [ ] Install maatwebsite/excel package
- [ ] Create PpabPaymentInternalExport class
- [ ] Create PpabPaymentPanitiaExport class
- [ ] Create PpabFinanceReportController
- [ ] Add routes with auth middleware
- [ ] Update modal buttons with route links
- [ ] Test Internal export generates correct columns
- [ ] Test Panitia export generates correct columns
- [ ] Verify currency formatting
- [ ] Verify row styling (yellow for DP in internal)
- [ ] Verify summary calculations
- [ ] Test with empty data
- [ ] Test with mixed DP and full payments
- [ ] Verify file downloads correctly
- [ ] Check filename format includes timestamp

## File Structure Summary

```
app/
├── Exports/
│   ├── PpabPaymentInternalExport.php    [NEW]
│   └── PpabPaymentPanitiaExport.php     [NEW]
└── Http/
    └── Controllers/
        └── PpabFinanceReportController.php [NEW]

resources/
└── views/
    └── filament/
        └── modals/
            └── ppab-payment-report.blade.php [MODIFY]

routes/
└── web.php [MODIFY]

composer.json [MODIFY - add maatwebsite/excel]
```

## Migration Notes

**Differences from Old System:**
1. Output format changed from PDF to Excel
2. No per-user auth level check (both types available)
3. Using Laravel Excel instead of DomPDF
4. Modern Filament modal integration
5. Cleaner separation of concerns (Export classes)

**Benefits:**
- Excel allows data manipulation by users
- Better for financial analysis
- Copy-paste friendly
- Formula support
- Professional formatting
- Easier to import into accounting software

## Next Steps

After approval:
1. Switch to Code mode
2. Install package
3. Create Export classes
4. Create Controller
5. Update routes
6. Wire modal buttons
7. Test thoroughly
