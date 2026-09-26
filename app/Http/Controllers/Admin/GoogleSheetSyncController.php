<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class GoogleSheetSyncController extends Controller
{
    public function index()
    {
        return view('admin.google-sheet-sync.index');
    }

    public function sync(Request $request)
    {
        $request->validate([
            'type' => 'required|in:work-orders,finance,attendance',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        try {
            $params = [];
            if ($request->filled('start_date')) {
                $params['--start'] = $request->start_date;
            }
            if ($request->filled('end_date')) {
                $params['--end'] = $request->end_date;
            }

            $command = match($request->type) {
                'finance' => 'sync:finance-sheet',
                'attendance' => 'sync:attendance-sheet',
                default => 'sync:work-orders-sheet',
            };
            
            Artisan::call($command, $params);
            $output = Artisan::output();

            $typeLabel = match($request->type) {
                'finance' => 'Keuangan',
                'attendance' => 'Presensi',
                default => 'Work Order',
            };
            
            return redirect()
                ->route('admin.google-sheet-sync.index')
                ->with('success', "Data $typeLabel berhasil disync ke Google Spreadsheet. " . trim($output));
        } catch (\Exception $e) {
            return redirect()
                ->route('admin.google-sheet-sync.index')
                ->with('error', 'Gagal sync: ' . $e->getMessage());
        }
    }
}
