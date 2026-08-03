@extends('layouts.app')

@section('title', 'Absensi Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Absensi Saya</h3>
    <div class="d-flex gap-2 align-items-center">
        @if(auth()->user()->face_descriptor)
            <span class="badge bg-success">Verifikasi wajah aktif</span>
            <button id="faceCheckinBtn" class="btn btn-success" {{ $todayRecord ? 'disabled' : '' }}>Check-in (Verifikasi Wajah)</button>
        @endif

        <form method="POST" action="{{ route('attendance.checkout') }}">
            @csrf
            <button class="btn btn-warning" {{ (!$todayRecord || $todayRecord->check_out) ? 'disabled' : '' }}>Check-out</button>
        </form>
    </div>
</div>

@if(!auth()->user()->face_descriptor && !$todayRecord)
<div class="alert alert-warning d-flex justify-content-between align-items-center mb-4">
    <div>
        <strong>Kamu belum mendaftarkan wajah.</strong>
        <p class="mb-0 small">Daftarkan wajah dulu sebelum bisa check-in — hanya perlu dilakukan sekali.</p>
    </div>
    <a href="{{ route('attendance.face-enroll') }}" class="btn btn-primary">Daftarkan Wajah Sekarang</a>
</div>
@endif

@if(auth()->user()->face_descriptor && !$todayRecord)
<div class="card shadow-sm mb-4 d-none" id="faceModalCard">
    <div class="card-body text-center">
        <p class="mb-2">Posisikan wajah kamu di kamera untuk check-in</p>
        <video id="video" width="320" height="240" autoplay muted class="border rounded mb-2 bg-dark"></video>
        <div id="faceStatus" class="text-muted small mb-2">Memuat model AI...</div>
        <div>
            <button id="faceCaptureConfirmBtn" class="btn btn-primary btn-sm" disabled>Verifikasi & Check-in</button>
            <button id="faceCancelBtn" class="btn btn-secondary btn-sm">Batal</button>
        </div>
    </div>
</div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Foto</th>
                    <th>Check-in</th>
                    <th>Check-out</th>
                    <th>Status</th>
                    <th>Catatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $a)
                    <tr>
                        <td>{{ $a->date->format('d M Y') }}</td>
                        <td>
                            @if($a->photo_url)
                                <a href="{{ $a->photo_url }}" target="_blank">
                                    <img src="{{ $a->photo_url }}" width="40" height="40" class="rounded" style="object-fit: cover;">
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $a->check_in ?? '-' }}</td>
                        <td>{{ $a->check_out ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ $a->status }}</span></td>
                        <td>{{ $a->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">Belum ada data absensi.</td></tr>
                @endforelse
            </tbody>
        </table>
        {{ $attendances->links() }}
    </div>
</div>
@endsection

@if(auth()->user()->face_descriptor && !$todayRecord)
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const MODEL_URL = "{{ asset('models') }}";
const openBtn = document.getElementById('faceCheckinBtn');
const modalCard = document.getElementById('faceModalCard');
const video = document.getElementById('video');
const statusEl = document.getElementById('faceStatus');
const confirmBtn = document.getElementById('faceCaptureConfirmBtn');
const cancelBtn = document.getElementById('faceCancelBtn');

let modelsLoaded = false;
let stream = null;

openBtn.addEventListener('click', async () => {
    modalCard.classList.remove('d-none');
    openBtn.disabled = true;

    if (!modelsLoaded) {
        statusEl.textContent = 'Memuat model AI...';
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
            await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
            modelsLoaded = true;
        } catch (e) {
            statusEl.textContent = 'Gagal memuat model AI.';
            return;
        }
    }

    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: {} });
        video.srcObject = stream;
        statusEl.textContent = 'Posisikan wajah kamu, lalu klik Verifikasi & Check-in.';
        confirmBtn.disabled = false;
    } catch (e) {
        statusEl.textContent = 'Gagal mengakses kamera: ' + e.message;
    }
});

cancelBtn.addEventListener('click', () => {
    if (stream) stream.getTracks().forEach(t => t.stop());
    modalCard.classList.add('d-none');
    openBtn.disabled = false;
});

confirmBtn.addEventListener('click', async () => {
    confirmBtn.disabled = true;
    statusEl.textContent = 'Mendeteksi wajah...';

    const detection = await faceapi
        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
        .withFaceLandmarks()
        .withFaceDescriptor();

    if (!detection) {
        statusEl.textContent = 'Wajah tidak terdeteksi, coba lagi.';
        confirmBtn.disabled = false;
        return;
    }

    const descriptor = Array.from(detection.descriptor);

    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    const photoDataUrl = canvas.toDataURL('image/jpeg', 0.8);

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('attendance.checkin') }}";

    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    const photoInput = document.createElement('input');
    photoInput.type = 'hidden';
    photoInput.name = 'photo';
    photoInput.value = photoDataUrl;
    form.appendChild(photoInput);

    descriptor.forEach((val, i) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `descriptor[${i}]`;
        input.value = val;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
});
</script>
@endsection
@endif
