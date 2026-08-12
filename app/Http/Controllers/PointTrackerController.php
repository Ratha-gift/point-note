<?php

namespace App\Http\Controllers;

use App\Models\PointEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Browsershot\Browsershot;

class PointTrackerController extends Controller
{
    // GET /point-tracker?date=2026-08-03
    public function index(Request $request)
    {
        $date = $request->input('date', now()->toDateString());

        $entries = PointEntry::whereDate('entry_date', $date)
            ->orderBy('id')
            ->get(['id', 'amount']);

        $total = $entries->sum('amount');

        return Inertia::render('PointTracker', [
            'date' => $date,
            'entries' => $entries,
            'total' => round($total, 2),
            'point' => round($total / PointEntry::DIVISOR, 1),
        ]);
    }

    // POST /point-tracker  { entry_date, amount }
    // `amount` may be a single number ("30") or a "+"-joined expression typed
    // in one go ("30+40+60"), which is summed into ONE saved entry (80+...)
    // instead of creating one row per number.
    public function store(Request $request)
    {
        $data = $request->validate([
            'entry_date' => 'required|date',
            'amount' => ['required', 'string', 'regex:/^\s*\d+(\.\d{1,2})?(\s*\+\s*\d+(\.\d{1,2})?)*\s*$/'],
        ]);

        $total = collect(explode('+', $data['amount']))
            ->map(fn ($v) => (float) trim($v))
            ->sum();

        if ($total <= 0) {
            return back()->withErrors(['amount' => 'សូមបញ្ចូលចំនួនទឹកប្រាក់ត្រឹមត្រូវ']);
        }

        $entry = PointEntry::create([
            'entry_date' => $data['entry_date'],
            'amount' => round($total, 2),
            'user_id' => $request->user()?->id,
        ]);

        return back()->with('entry', $entry);
    }

    // PUT /point-tracker/{pointEntry}  { amount }
    public function update(Request $request, PointEntry $pointEntry)
    {
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $pointEntry->update(['amount' => $data['amount']]);

        return back();
    }

    // DELETE /point-tracker/{pointEntry}
    public function destroy(PointEntry $pointEntry)
    {
        $pointEntry->delete();

        return back();
    }

    // GET /point-tracker/report?month=2026-08
    public function report(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $rows = $this->monthlyRows($month);

        return Inertia::render('PointTrackerReport', [
            'month' => $month,
            'rows' => $rows,
            'monthTotal' => round($rows->sum('total'), 2),
            'monthPoint' => round($rows->sum('point'), 1),
        ]);
    }

    // GET /point-tracker/report/export-pdf?month=2026-08
    // Rendered via headless Chrome (Browsershot/Puppeteer) rather than
    // dompdf: Khmer text needs real OpenType shaping (subscript consonant
    // stacking, vowel reordering) that dompdf's layout engine can't do,
    // but a real browser engine handles natively — same as the live page.
    public function exportPdf(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $rows = $this->monthlyRows($month);

        $html = view('exports.point-tracker-report', [
            'month' => $month,
            'rows' => $rows,
            'monthTotal' => round($rows->sum('total'), 2),
            'monthPoint' => round($rows->sum('point'), 1),
        ])->render();

        $pdf = Browsershot::html($html)
            ->format('A4')
            ->showBackground()
            ->pdf();

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"point-tracker-{$month}.pdf\"",
        ]);
    }

    // GET /point-tracker/report/export-excel?month=2026-08
    public function exportExcel(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $rows = $this->monthlyRows($month);
        $monthTotal = round($rows->sum('total'), 2);
        $monthPoint = round($rows->sum('point'), 1);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Point Tracker');

        $sheet->fromArray(['Date', 'Total ($)', 'Point'], null, 'A1');
        $sheet->getStyle('A1:C1')->getFont()->setBold(true);

        $r = 2;
        foreach ($rows as $row) {
            $sheet->fromArray([$row['date'], $row['total'], $row['point']], null, "A{$r}");
            $r++;
        }

        $sheet->fromArray(['Month Total', $monthTotal, $monthPoint], null, "A{$r}");
        $sheet->getStyle("A{$r}:C{$r}")->getFont()->setBold(true);

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, "point-tracker-{$month}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Shared per-day aggregation for a given "Y-m" month, used by the
    // report page and both export formats.
    private function monthlyRows(string $month)
    {
        $start = Carbon::parse($month . '-01')->startOfMonth();
        $end = $start->copy()->endOfMonth();

        return PointEntry::whereBetween('entry_date', [$start, $end])
            ->selectRaw('entry_date, SUM(amount) as total')
            ->groupBy('entry_date')
            ->orderBy('entry_date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->entry_date->format('Y-m-d'),
                'total' => (float) $row->total,
                'point' => round($row->total / PointEntry::DIVISOR, 1),
            ]);
    }
}
