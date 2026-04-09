<?php

namespace App\Services;

use App\Models\InvoiceSequence;
use App\Models\Order;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Assigna un número correlatiu per sèrie (preparació per facturació / ticket).
 * No substitueix Veri*Factu ni requisits legals addicionals: només evita forats
 * casuals i separa el número fiscal de l'id intern de la comanda.
 */
class FiscalInvoiceNumberAssigner
{
    public function assignIfMissing(Order $order): void
    {
        if (! config('ticket.assign_fiscal_number', true)) {
            return;
        }

        if ($order->status !== 'Pagat') {
            return;
        }

        if ($order->fiscal_full_number) {
            return;
        }

        $series = (string) config('ticket.invoice_series', 'QS');
        $pad = max(1, (int) config('ticket.invoice_number_pad', 8));

        DB::transaction(function () use ($order, $series, $pad) {
            $order->refresh();

            if ($order->fiscal_full_number) {
                return;
            }

            $seq = InvoiceSequence::query()->where('series', $series)->lockForUpdate()->first();

            if (! $seq) {
                try {
                    InvoiceSequence::query()->create([
                        'series' => $series,
                        'next_number' => 2,
                    ]);
                    $n = 1;
                } catch (QueryException $e) {
                    if (! $this->isUniqueConstraintViolation($e)) {
                        throw $e;
                    }
                    $seq = InvoiceSequence::query()->where('series', $series)->lockForUpdate()->firstOrFail();
                    $n = (int) $seq->next_number;
                    $seq->next_number = $n + 1;
                    $seq->save();
                }
            } else {
                $n = (int) $seq->next_number;
                $seq->next_number = $n + 1;
                $seq->save();
            }

            $full = $series.str_pad((string) $n, $pad, '0', STR_PAD_LEFT);

            $order->forceFill([
                'fiscal_series' => $series,
                'fiscal_sequence' => $n,
                'fiscal_full_number' => $full,
            ])->save();
        });
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'unique')
            || str_contains($message, 'duplicate')
            || $e->getCode() === '23000';
    }
}
