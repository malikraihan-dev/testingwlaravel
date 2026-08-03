<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinanceRecord;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
}
