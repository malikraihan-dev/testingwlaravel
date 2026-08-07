<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceRecord;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminFinanceController extends Controller
{
    public function index(Request $request)
    {
        $query = FinanceRecord::with('user')->orderByDesc('date');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->paginate(15)->withQueryString();
        $users = User::orderBy('name')->get();

        $totalIncomeApproved = FinanceRecord::where('type', 'pemasukan')->where('status', 'approved')->sum('amount');
        $totalExpenseApproved = FinanceRecord::where('type', 'pengeluaran')->where('status', 'approved')->sum('amount');
        $pendingCount = FinanceRecord::where('status', 'finalized')->count();

        return view('admin.finance.index', compact('records', 'users', 'totalIncomeApproved', 'totalExpenseApproved', 'pendingCount'));
    }

    public function approve(FinanceRecord $finance)
    {
        if ($finance->status !== 'finalized') {
            return back()->with('error', 'Hanya data berstatus "finalized" yang bisa disetujui.');
        }

        $finance->update(['status' => 'approved', 'admin_note' => null]);

        return back()->with('success', 'Data berhasil disetujui.');
    }

    public function reject(Request $request, FinanceRecord $finance)
    {
        $request->validate([
            'admin_note' => ['required', 'string', 'max:500'],
        ]);

        if ($finance->status !== 'finalized') {
            return back()->with('error', 'Hanya data berstatus "finalized" yang bisa ditolak.');
        }

        $finance->update([
            'status' => 'rejected',
            'admin_note' => $request->admin_note,
        ]);

        return back()->with('success', 'Data ditolak, user bisa merevisi dan mengirim ulang.');
    }

    public function destroy(FinanceRecord $finance)
    {
        if ($finance->attachment_path) {
            Storage::disk('public')->delete($finance->attachment_path);
        }

        $finance->delete();

        return back()->with('success', 'Catatan keuangan berhasil dihapus.');
    }

    private function monthlyRecords(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $records = FinanceRecord::with('user')
            ->whereYear('date', $year)
            ->whereMonth('date', $monthNum)
            ->orderBy('date')
            ->get();

        $monthLabel = Carbon::createFromDate($year, $monthNum, 1)->translatedFormat('F Y');

        $totalIncome = $records->where('type', 'pemasukan')->where('status', 'approved')->sum('amount');
        $totalExpense = $records->where('type', 'pengeluaran')->where('status', 'approved')->sum('amount');

        return [$records, $month, $monthLabel, $totalIncome, $totalExpense];
    }

    public function exportExcel(Request $request)
    {
        [$records, $month, $monthLabel, $totalIncome, $totalExpense] = $this->monthlyRecords($request);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Keuangan');

        $sheet->setCellValue('A1', 'Laporan Keuangan - '.$monthLabel);
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $headers = ['Tanggal', 'User', 'Tipe', 'Kategori', 'Jumlah', 'Status', 'Catatan Admin'];
        $sheet->fromArray($headers, null, 'A3');
        $sheet->getStyle('A3:G3')->getFont()->setBold(true);

        $row = 4;
        foreach ($records as $r) {
            $sheet->fromArray([
                $r->date->format('d-m-Y'),
                $r->user->name,
                ucfirst($r->type),
                $r->category ?? '-',
                (float) $r->amount,
                ucfirst($r->status),
                $r->admin_note ?? '-',
            ], null, "A{$row}");
            $row++;
        }

        $row += 1;
        $sheet->setCellValue("A{$row}", 'Total Pemasukan (disetujui)');
        $sheet->setCellValue("E{$row}", $totalIncome);
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
        $row++;
        $sheet->setCellValue("A{$row}", 'Total Pengeluaran (disetujui)');
        $sheet->setCellValue("E{$row}", $totalExpense);
        $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'laporan-keuangan-'.$month.'.xlsx';
        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request)
    {
        [$records, $month, $monthLabel, $totalIncome, $totalExpense] = $this->monthlyRecords($request);

        $pdf = Pdf::loadView('exports.finance-pdf', compact('records', 'monthLabel', 'totalIncome', 'totalExpense'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('laporan-keuangan-'.$month.'.pdf');
    }
}
