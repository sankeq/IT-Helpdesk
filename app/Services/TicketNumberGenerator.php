<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Generate unique ticket numbers using a transaction-safe counter table.
 * Format: IKI-IT-YYYY-NNNNNN
 * Uses database row-level locking to prevent duplicates under concurrent requests.
 */
class TicketNumberGenerator
{
    /**
     * Generate the next unique ticket number.
     * Uses pessimistic locking on the counter row.
     */
    public function generate(): string
    {
        // Use business timezone (Asia/Makassar) for year determination
        $year = now(config('app.timezone', 'Asia/Makassar'))->year;
        $prefix = "IKI-IT-{$year}";

        return DB::transaction(function () use ($prefix) {
            // Lock the row for this year's prefix
            $sequence = DB::table('ticket_number_sequences')
                ->where('prefix', $prefix)
                ->lockForUpdate()
                ->first();

            if ($sequence) {
                $nextNumber = $sequence->last_number + 1;
                DB::table('ticket_number_sequences')
                    ->where('prefix', $prefix)
                    ->update([
                        'last_number' => $nextNumber,
                        'updated_at' => now(),
                    ]);
            } else {
                $nextNumber = 1;
                DB::table('ticket_number_sequences')->insert([
                    'prefix' => $prefix,
                    'last_number' => $nextNumber,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $prefix.'-'.str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        });
    }
}
