<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('user')->orderByDesc('date');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        $attendances = $query->paginate(15)->withQueryString();
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.attendances.index', compact('attendances', 'users'));
    }

    public function update(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'status' => ['required', 'in:hadir,izin,sakit,alpa'],
            'notes' => ['nullable', 'string'],
        ]);

        $attendance->update($data);

        return back()->with('success', 'Status kehadiran berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return back()->with('success', 'Data kehadiran berhasil dihapus.');
    }

    /**
     * JSON data for the admin dashboard chart:
     * count of each status over the last 7 days.
     */
    public function chartData()
    {
        $days = collect(range(6, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $labels = $days->map(fn ($d) => Carbon::parse($d)->format('d M'));

        $statuses = ['hadir', 'izin', 'sakit', 'alpa'];
        $datasets = [];

        foreach ($statuses as $status) {
            $datasets[$status] = $days->map(function ($day) use ($status) {
                return Attendance::whereDate('date', $day)->where('status', $status)->count();
            })->values();
        }

        return response()->json([
            'labels' => $labels->values(),
            'datasets' => $datasets,
        ]);
    }

    private function monthlyRecords(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $records = Attendance::with('user')
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date')
            ->get();

        $monthLabel = Carbon::createFromDate($year, $monthNum, 1)->translatedFormat('F Y');

        return [$records, $month, $monthLabel];
    }

    public function exportExcel(Request $request)
    {
        [$records, $month, $monthLabel] = $this->monthlyRecords($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Absensi');

        $sheet->setCellValue('A1', 'Laporan Absensi - '.$monthLabel);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headers = ['Tanggal', 'Nama', 'Check-in', 'Check-out', 'Status', 'Catatan'];
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getStyle('A3:F3')->getFont()->setBold(true);

        $row = 4;
        foreach ($records as $r) {
            $sheet->fromArray([
                $r->date->format('d-m-Y'),
                $r->user->name,
                $r->check_in ?? '-',
                $r->check_out ?? '-',
                ucfirst($r->status),
                $r->notes ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'laporan-absensi-'.$month.'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request)
    {
        [$records, $month, $monthLabel] = $this->monthlyRecords($request);

        $pdf = Pdf::loadView('exports.attendance-pdf', compact('records', 'monthLabel'))
            ->setPaper('a4', 'portrait');

        return $pdf->download('laporan-absensi-'.$month.'.pdf');
    }
}
