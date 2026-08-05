@extends('layouts.app')

@section('title', 'Kelola Wajah User')

@section('content')
<div class="max-w-lg mx-auto">
    <h2 class="text-2xl font-bold tracking-tight mb-1">{{ $user->hasFaceEnrolled() ? 'Ganti' : 'Daftarkan' }} Wajah</h2>
    <p class="text-slate-500 mb-6 text-sm">Untuk user: <strong>{{ $user->name }}</strong> ({{ $user->email }})</p>

    <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm">
        @if($user->face_photo_url)
            <div class="mb-4">
                <p class="text-xs font-bold text-slate-500 uppercase mb-2">Foto Saat Ini</p>
                <img src="{{ $user->face_photo_url }}" class="w-24 h-24 rounded-xl object-cover border border-slate-200">
            </div>
        @endif

        <label class="block text-sm font-bold mb-1">Upload Foto Baru</label>
        <input id="file-input" type="file" accept="image/*" class="w-full px-4 py-2.5 border border-slate-300 rounded-xl mb-3">

        <div class="w-full aspect-video bg-slate-100 rounded-xl overflow-hidden mb-3 flex items-center justify-center relative">
            <img id="preview-img" class="max-w-full max-h-full hidden">
            <canvas id="overlay" class="absolute inset-0 w-full h-full"></canvas>
            <p id="preview-placeholder" class="text-slate-400 text-sm">Belum ada foto dipilih</p>
        </div>

        <p id="status" class="text-sm text-slate-500 mb-4">Pilih foto yang wajahnya terlihat jelas dan menghadap kamera.</p>

        <button id="save-btn" disabled class="w-full py-3 bg-slate-900 text-white font-bold rounded-xl disabled:opacity-40">
            Simpan
        </button>

        <a href="{{ route('admin.face.index') }}" class="block mt-3 text-sm text-slate-500 hover:underline text-center">Batal</a>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const fileInput = document.getElementById('file-input');
const previewImg = document.getElementById('preview-img');
const placeholder = document.getElementById('preview-placeholder');
const overlay = document.getElementById('overlay');
const statusEl = document.getElementById('status');
const saveBtn = document.getElementById('save-btn');

let modelsLoaded = false;
let currentDescriptor = null;
let currentPhotoDataUrl = null;

async function ensureModels() {
    if (modelsLoaded) return;
    statusEl.textContent = 'Memuat model AI...';
    await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
    await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
    await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
    modelsLoaded = true;
}

fileInput.addEventListener('change', async () => {
    const file = fileInput.files[0];
    if (!file) return;

    currentDescriptor = null;
    saveBtn.disabled = true;

    const reader = new FileReader();
    reader.onload = async (e) => {
        currentPhotoDataUrl = e.target.result;
        previewImg.src = currentPhotoDataUrl;
        previewImg.classList.remove('hidden');
        placeholder.classList.add('hidden');

        await new Promise(resolve => { previewImg.onload = resolve; });

        try {
            await ensureModels();
        } catch (err) {
            statusEl.textContent = 'Gagal memuat model AI (cek folder public/models).';
            return;
        }

        statusEl.textContent = 'Mendeteksi wajah pada foto...';

        const result = await faceapi
            .detectSingleFace(previewImg, new faceapi.TinyFaceDetectorOptions())
            .withFaceLandmarks()
            .withFaceDescriptor();

        const displaySize = { width: previewImg.clientWidth, height: previewImg.clientHeight };
        faceapi.matchDimensions(overlay, displaySize);
        const ctx = overlay.getContext('2d');
        ctx.clearRect(0, 0, overlay.width, overlay.height);

        if (result) {
            const resized = faceapi.resizeResults(result, displaySize);
            faceapi.draw.drawDetections(overlay, resized);
            currentDescriptor = Array.from(result.descriptor);
            saveBtn.disabled = false;
            statusEl.textContent = 'Wajah terdeteksi. Klik Simpan untuk melanjutkan.';
        } else {
            statusEl.textContent = 'Wajah tidak terdeteksi di foto ini. Coba foto lain yang lebih jelas.';
        }
    };
    reader.readAsDataURL(file);
});

saveBtn.addEventListener('click', async () => {
    if (!currentDescriptor || !currentPhotoDataUrl) return;

    saveBtn.disabled = true;
    saveBtn.textContent = 'Menyimpan...';

    try {
        const res = await fetch("{{ route('admin.face.update', $user) }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({ face_descriptor: currentDescriptor, photo: currentPhotoDataUrl }),
        });

        const data = await res.json();

        if (res.ok && data.success) {
            window.location.href = "{{ route('admin.face.index') }}";
        } else {
            statusEl.textContent = data.message || 'Gagal menyimpan.';
            saveBtn.disabled = false;
            saveBtn.textContent = 'Simpan';
        }
    } catch (e) {
        statusEl.textContent = 'Terjadi kesalahan jaringan.';
        saveBtn.disabled = false;
        saveBtn.textContent = 'Simpan';
    }
});
</script>
@endsection
