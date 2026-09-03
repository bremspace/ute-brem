<?php

namespace App\Services;

use App\Models\BackOfficeCashAccount;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Double-entry posting engine.
 *
 * Menjamin setiap jurnal seimbang (debit == credit) dan idempotent per
 * (source_type, source_id) — transaksi operasional boleh diposting ulang
 * tanpa membuat jurnal ganda (ada unique constraint source_type+source_id).
 */
class LedgerService
{
    public function __construct(
        private readonly BackOfficeCashService $cashService
    ) {}

    /** Ambil akun COA berdasar kode, lempar jika tak ada. */
    public function account(string $code): ChartOfAccount
    {
        $acc = ChartOfAccount::where('code', $code)->first();
        if (! $acc) {
            throw ValidationException::withMessages(['account' => "Akun COA '$code' tidak ditemukan."]);
        }
        return $acc;
    }

    /** Resolve akun COA untuk sebuah cash account back-office. */
    public function cashAccountToCoa(BackOfficeCashAccount $account): ChartOfAccount
    {
        if ($account->chart_of_account_id) {
            $coa = ChartOfAccount::find($account->chart_of_account_id);
            if ($coa) {
                return $coa;
            }
        }
        // Fallback berdasar tipe kalau pengguna belum memetakan manual
        $code = match ($account->type) {
            'bank' => '1102',
            'ewallet' => '1103',
            default => '1101',
        };
        return $this->account($code);
    }

    /**
     * Posting jurnal seimbang.
     *
     * @param string $sourceType ex. 'sale'
     * @param int|string|null $sourceId
     * @param string $codeSuffix ex. sale_code -> journal code JRL-...
     * @param string $date Y-m-d
     * @param string $description
     * @param array $cashAccounts cash accounts terlibat (untuk kode timeout) — optional
     * @param array $lines [['account' => code, 'debit' => n|0, 'credit' => n|0, 'memo' => ?], ...]
     * @param int|null $createdBy
     */
    public function post(
        string $sourceType,
        $sourceId,
        string $codeSuffix,
        string $date,
        string $description,
        array $lines,
        ?int $createdBy = null
    ): ?JournalEntry {
        // Idempotent: jangan posting ulang source yang sama
        if ($sourceId !== null && JournalEntry::where('source_type', $sourceType)->where('source_id', $sourceId)->exists()) {
            return null;
        }

        $debitSum = 0.0;
        $creditSum = 0.0;
        $normalized = [];

        foreach ($lines as $l) {
            $acct = $this->account($l['account']);
            $debit = (float) ($l['debit'] ?? 0);
            $credit = (float) ($l['credit'] ?? 0);
            $debitSum += $debit;
            $creditSum += $credit;
            $normalized[] = [
                'account_id' => $acct->id,
                'debit' => $debit,
                'credit' => $credit,
                'memo' => $l['memo'] ?? null,
            ];
        }

        // Abaikan posting kosong
        if ($debitSum == 0 && $creditSum == 0) {
            return null;
        }

        // Cek keseimbangan; toleransi pembulatan kecil
        if (abs($debitSum - $creditSum) > 0.009) {
            throw ValidationException::withMessages([
                'journal' => "Jurnal tidak seimbang (Debit ".number_format($debitSum, 2)." vs Kredit ".number_format($creditSum, 2).").",
            ]);
        }

        $code = 'JRL-' . date('Ymd') . '-' . strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $codeSuffix));

        return DB::transaction(function () use ($sourceType, $sourceId, $code, $codeSuffix, $date, $description, $debitSum, $normalized, $createdBy) {
            $entry = JournalEntry::create([
                'journal_code' => $code,
                'journal_date' => $date,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'source_reference' => $codeSuffix,
                'description' => $description,
                'debit_total' => $debitSum,
                'credit_total' => $debitSum,
                'created_by' => $createdBy,
            ]);

            foreach ($normalized as $n) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'account_id' => $n['account_id'],
                    'debit' => $n['debit'],
                    'credit' => $n['credit'],
                    'memo' => $n['memo'],
                ]);
            }

            return $entry;
        });
    }

    /** Balik jurnal bila transaksi dibatalkan (void) — dibuat jurnal pembalik. */
    public function reverse(
        string $sourceType,
        $sourceId,
        string $codeSuffix,
        string $date,
        string $description,
        ?int $createdBy = null
    ): ?JournalEntry {
        $entry = JournalEntry::where('source_type', $sourceType)->where('source_id', $sourceId)->with('lines')->first();
        if (! $entry) {
            return null;
        }

        $reverseLines = [];
        foreach ($entry->lines as $line) {
            $reverseLines[] = [
                'account' => (string) $line->account_id,
                'debit' => $line->credit,
                'credit' => $line->debit,
                'memo' => 'Reversal: ' . ($line->memo ?? ''),
            ];
        }

        // Map account_id -> code
        $codes = [];
        foreach ($reverseLines as $i => $rl) {
            $acc = ChartOfAccount::find((int) $rl['account']);
            if ($acc) {
                $codes[$i] = $acc->code;
            }
        }
        $final = [];
        foreach ($reverseLines as $i => $rl) {
            if (! isset($codes[$i])) {
                continue;
            }
            $final[] = [
                'account' => $codes[$i],
                'debit' => $rl['debit'],
                'credit' => $rl['credit'],
                'memo' => $rl['memo'],
            ];
        }

        return $this->post(
            $sourceType . '_reversal',
            $sourceId,
            'REV-' . $codeSuffix,
            $date,
            $description,
            $final,
            $createdBy
        );
    }

    /** Path logistik: pilih kas account sesuai metode bayar (delegasi ke BackOfficeCashService). */
    public function cashAccountForMethod(string $method): ?BackOfficeCashAccount
    {
        return $this->cashService->resolveAccountForPaymentMethod($method);
    }
}
