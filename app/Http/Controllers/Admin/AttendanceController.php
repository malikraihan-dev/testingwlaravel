<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

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

        $labels = $days->map(fn ($d) => \Carbon\Carbon::parse($d)->format('d M'));

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
}
