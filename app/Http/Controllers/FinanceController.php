<?php

namespace App\Http\Controllers;

use App\Models\FinanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FinanceController extends Controller
{
    public function index()
    {
        $records = FinanceRecord::where('user_id', Auth::id())
            ->orderByDesc('date')
            ->paginate(10);

        $totalIncome = FinanceRecord::where('user_id', Auth::id())
            ->where('type', 'pemasukan')->where('status', 'approved')->sum('amount');

        $totalExpense = FinanceRecord::where('user_id', Auth::id())
            ->where('type', 'pengeluaran')->where('status', 'approved')->sum('amount');

        $pendingCount = FinanceRecord::where('user_id', Auth::id())
            ->where('status', 'finalized')->count();

        return view('finance.index', compact('records', 'totalIncome', 'totalExpense', 'pendingCount'));
    }

    public function create()
    {
        return view('finance.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        if ($request->hasFile('attachment')) {
            $data['attachment_path'] = $request->file('attachment')->store('finance-attachments', 'public');
            $data['attachment_original_name'] = $request->file('attachment')->getClientOriginalName();
        }

        $data['user_id'] = Auth::id();
        $data['status'] = 'draft';

        FinanceRecord::create($data);

        return redirect()->route('finance.index')->with('success', 'Catatan keuangan berhasil disimpan sebagai draft.');
    }

    public function edit(FinanceRecord $finance)
    {
        abort_unless($finance->canBeEditedBy(Auth::user()), 403, 'Data ini tidak bisa diedit (sudah difinalisasi/disetujui, atau bukan milikmu).');

        return view('finance.edit', compact('finance'));
    }

    public function update(Request $request, FinanceRecord $finance)
    {
        abort_unless($finance->canBeEditedBy(Auth::user()), 403, 'Data ini tidak bisa diedit.');

        $data = $this->validateData($request);

        if ($request->hasFile('attachment')) {
            if ($finance->attachment_path) {
                Storage::disk('public')->delete($finance->attachment_path);
            }
            $data['attachment_path'] = $request->file('attachment')->store('finance-attachments', 'public');
            $data['attachment_original_name'] = $request->file('attachment')->getClientOriginalName();
        }

        // Editing a rejected record resets it back to draft for re-review.
        if ($finance->status === 'rejected') {
            $data['status'] = 'draft';
            $data['admin_note'] = null;
        }

        $finance->update($data);

        return redirect()->route('finance.index')->with('success', 'Catatan keuangan berhasil diperbarui.');
    }

    public function destroy(FinanceRecord $finance)
    {
        abort_unless($finance->canBeEditedBy(Auth::user()), 403, 'Data ini tidak bisa dihapus.');

        if ($finance->attachment_path) {
            Storage::disk('public')->delete($finance->attachment_path);
        }

        $finance->delete();

        return back()->with('success', 'Catatan keuangan berhasil dihapus.');
    }

    public function finalize(FinanceRecord $finance)
    {
        abort_unless($finance->user_id === Auth::id(), 403);

        if (! $finance->attachment_path) {
            return back()->with('error', 'Finalisasi butuh file pendukung. Upload dulu bukti/file pendukungnya.');
        }

        if (! in_array($finance->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Data ini tidak bisa difinalisasi ulang.');
        }

        $finance->update([
            'status' => 'finalized',
            'admin_note' => null,
        ]);

        return back()->with('success', 'Berhasil difinalisasi dan dikirim untuk direview admin.');
    }

    public function download(FinanceRecord $finance)
    {
        $user = Auth::user();
        abort_unless($finance->user_id === $user->id || $user->isAdmin(), 403);
        abort_unless($finance->attachment_path, 404);

        return Storage::disk('public')->download($finance->attachment_path, $finance->attachment_original_name ?? 'lampiran');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:pemasukan,pengeluaran'],
            'category' => ['nullable', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
    }
}
