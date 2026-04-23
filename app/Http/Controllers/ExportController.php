<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function csv(Request $request)
    {

        $dateFrom = $request->get('from', now()->startOfMonth()->format('Y-m-d'));
        $dateTo   = $request->get('to', now()->endOfMonth()->format('Y-m-d'));

        $transactions = Transaction::with('category')
        // ->where('user_id', auth()->id())
        ->whereDate('date', '>=', $dateFrom)
        ->whereDate('date', '<=', $dateTo)
        ->orderBy('date', 'desc')
        ->get();

        // debug sementara — tengok berapa record dapat
        dd(\App\Models\Transaction::first()->toArray(), // tengok semua attribute values
            auth()->id(), // tengok user_id semasa
        );
        
        $filename = 'transactions-' . $dateFrom . '-to-' . $dateTo . '.csv';

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Date', 'Type', 'Category', 'Description', 'Amount (RM)']);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->date,
                    ucfirst($t->type),
                    $t->category?->name ?? '-',
                    $t->description ?? '-',
                    number_format($t->amount, 2),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}