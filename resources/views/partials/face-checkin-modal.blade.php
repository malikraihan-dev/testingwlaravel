{{-- Include this partial on any page that has a #face-checkin-trigger button. --}}
<div id="face-checkin-modal" class="hidden fixed inset-0 bg-black/60 z-[200] flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl p-6 w-full max-w-md">
        <div class="flex justify-between items-center mb-4">
            <h3 class="font-bold text-lg">Verifikasi Wajah untuk Check-in</h3>
            <button id="face-modal-close" class="text-slate-400 hover:text-slate-700">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="relative w-full aspect-video bg-slate-900 rounded-xl overflow-hidden mb-3">
            <video id="face-video" class="w-full h-full object-cover" autoplay muted playsinline></video>
            <canvas id="face-overlay" class="absolute inset-0 w-full h-full"></canvas>
        </div>

        <p id="face-status" class="text-sm text-slate-500 mb-3 text-center">Memuat model AI...</p>

        <button id="face-verify-btn" disabled class="w-full py-3 bg-slate-900 text-white font-bold rounded-xl disabled:opacity-40">
            Verifikasi &amp; Check-in
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
(function () {
    const trigger = document.getElementById('face-checkin-trigger');
    if (!trigger) return;

    const modal = document.getElementById('face-checkin-modal');
    const closeBtn = document.getElementById('face-modal-close');
    const video = document.getElementById('face-video');
    const overlay = document.getElementById('face-overlay');
    const statusEl = document.getElementById('face-status');
    const verifyBtn = document.getElementById('face-verify-btn');

    let modelsLoaded = false;
    let currentDescriptor = null;
    let stream = null;

    async function loadModels() {
        if (modelsLoaded) return;
        await faceapi.nets.tinyFaceDetector.loadFromUri('/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/models');
        modelsLoaded = true;
    }

    async function openModal() {
        modal.classList.remove('hidden');
        statusEl.textContent = 'Memuat model AI...';
        verifyBtn.disabled = true;

        try {
            await loadModels();
        } catch (e) {
            statusEl.textContent = 'Gagal memuat model AI (cek folder public/models).';
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: {} });
            video.srcObject = stream;
        } catch (e) {
            statusEl.textContent = 'Tidak bisa mengakses kamera.';
            return;
        }

        statusEl.textContent = 'Posisikan wajah kamu di tengah frame.';
        detectLoop();
    }

    function closeModal() {
        modal.classList.add('hidden');
        if (stream) {
            stream.getTracks().forEach(t => t.stop());
            stream = null;
        }
    }

    async function detectLoop() {
        const options = new faceapi.TinyFaceDetectorOptions();

        const interval = setInterval(async () => {
            if (modal.classList.contains('hidden')) {
                clearInterval(interval);
                return;
            }

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
                verifyBtn.disabled = false;
                statusEl.textContent = 'Wajah terdeteksi. Klik untuk verifikasi.';
            } else {
                currentDescriptor = null;
                verifyBtn.disabled = true;
                statusEl.textContent = 'Wajah belum terdeteksi...';
            }
        }, 500);
    }

    trigger.addEventListener('click', openModal);
    closeBtn.addEventListener('click', closeModal);

    verifyBtn.addEventListener('click', async () => {
        if (!currentDescriptor) return;

        verifyBtn.disabled = true;
        verifyBtn.textContent = 'Memverifikasi...';

        try {
            const res = await fetch("{{ route('attendance.checkin') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ face_descriptor: currentDescriptor }),
            });

            const data = await res.json();

            if (res.ok && data.success) {
                window.location.reload();
            } else {
                statusEl.textContent = data.message || 'Verifikasi gagal, wajah tidak cocok.';
                verifyBtn.disabled = false;
                verifyBtn.textContent = 'Verifikasi & Check-in';
            }
        } catch (e) {
            statusEl.textContent = 'Terjadi kesalahan jaringan.';
            verifyBtn.disabled = false;
            verifyBtn.textContent = 'Verifikasi & Check-in';
        }
    });
})();
</script>
