@extends('layouts.app')

@section('title', 'Aktifkan Verifikasi Wajah')

@section('content')
<div class="max-w-lg mx-auto">
    <h2 class="text-2xl font-bold tracking-tight mb-2">Aktifkan Verifikasi Wajah</h2>
    <p class="text-slate-500 mb-6 text-sm">
        Wajah kamu akan direkam sebagai data fitur (128 angka) beserta foto untuk referensi admin.
    </p>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm text-center">
        <div class="relative w-full aspect-video bg-slate-900 rounded-xl overflow-hidden mb-4">
            <video id="video" class="w-full h-full object-cover" autoplay muted playsinline></video>
            <canvas id="overlay" class="absolute inset-0 w-full h-full"></canvas>
        </div>

        <p id="status" class="text-sm text-slate-500 mb-4">Memuat model AI...</p>

        <button id="capture-btn" disabled class="w-full py-3 bg-slate-900 text-white font-bold rounded-xl disabled:opacity-40">
            Ambil &amp; Simpan Wajah
        </button>

        <a href="{{ route('attendance.index') }}" class="block mt-3 text-sm text-slate-500 hover:underline">Batal</a>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const statusEl = document.getElementById('status');
const captureBtn = document.getElementById('capture-btn');
const video = document.getElementById('video');
const overlay = document.getElementById('overlay');

let currentDescriptor = null;

async function init() {
    try {
        await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
    } catch (e) {
        statusEl.textContent = 'Gagal memuat model AI. Pastikan folder public/models sudah lengkap.';
        return;
    }

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ video: {} });
        video.srcObject = stream;
    } catch (e) {
        statusEl.textContent = 'Tidak bisa mengakses kamera. Izinkan akses kamera di browser.';
        return;
    }

    statusEl.textContent = 'Posisikan wajah kamu di tengah frame.';
    detectLoop();
}

async function detectLoop() {
    const options = new faceapi.TinyFaceDetectorOptions();

    setInterval(async () => {
        const result = await faceapi
            .detectSingleFace(video, options)
            .withFaceLandmarks()
            .withFaceDescriptor();

        const displaySize = { width: video.clientWidth, height: video.clientHeight };
        faceapi.matchDimensions(overlay, displaySize);

        const ctx = overlay.getContext('2d');
        ctx.clearRect(0, 0, overlay.width, overlay.height);

        if (result) {
            const resized = faceapi.resizeResults(result, displaySize);
            faceapi.draw.drawDetections(overlay, resized);
            currentDescriptor = Array.from(result.descriptor);
            captureBtn.disabled = false;
            statusEl.textContent = 'Wajah terdeteksi. Klik tombol untuk menyimpan.';
        } else {
            currentDescriptor = null;
            captureBtn.disabled = true;
            statusEl.textContent = 'Wajah belum terdeteksi...';
        }
    }, 500);
}

function captureSnapshot() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    return canvas.toDataURL('image/jpeg', 0.8);
}

captureBtn.addEventListener('click', async () => {
    if (!currentDescriptor) return;

    captureBtn.disabled = true;
    captureBtn.textContent = 'Menyimpan...';

    try {
        const res = await fetch("{{ route('attendance.face-enroll.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ face_descriptor: currentDescriptor, photo: captureSnapshot() }),
        });

        if (res.ok) {
            window.location.href = "{{ route('attendance.index') }}";
        } else {
            statusEl.textContent = 'Gagal menyimpan data wajah, coba lagi.';
            captureBtn.disabled = false;
            captureBtn.textContent = 'Ambil & Simpan Wajah';
        }
    } catch (e) {
        statusEl.textContent = 'Terjadi kesalahan jaringan.';
        captureBtn.disabled = false;
        captureBtn.textContent = 'Ambil & Simpan Wajah';
    }
});

init();
</script>
@endsection
