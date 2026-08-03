<div class="fixed bottom-6 right-6 z-[100]">
    <button id="ai-chat-bubble" class="w-16 h-16 rounded-full bg-slate-900 text-white flex items-center justify-center shadow-2xl hover:scale-105 active:scale-95 transition-all">
        <span class="material-symbols-outlined text-3xl">auto_awesome</span>
    </button>

    <div id="ai-panel" class="hidden absolute bottom-20 right-0 w-96 max-w-[90vw] bg-white border border-slate-200 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
        <div class="bg-slate-900 p-4 flex items-center gap-3">
            <span class="material-symbols-outlined text-white">smart_toy</span>
            <div>
                <p class="text-sm font-bold text-white">Workforce AI Assistant</p>
                <p class="text-xs text-white/60">Ditenagai Claude</p>
            </div>
            <button id="ai-close" class="ml-auto text-white/70 hover:text-white">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div id="ai-messages" class="h-80 p-4 overflow-y-auto bg-slate-50 space-y-3 text-sm">
            <div class="flex gap-2">
                <div class="w-7 h-7 rounded-full bg-slate-900 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-sm">smart_toy</span>
                </div>
                <div class="bg-white p-3 rounded-tr-xl rounded-b-xl border border-slate-200">
                    Halo! Tanyakan apa saja soal data kehadiran tim kamu hari ini.
                </div>
            </div>
        </div>

        <form id="ai-form" class="p-3 border-t border-slate-200 flex gap-2">
            <input id="ai-input" type="text" autocomplete="off" class="flex-1 bg-slate-100 border-none rounded-lg py-2 px-3 text-sm focus:ring-2 focus:ring-slate-900" placeholder="Tanya sesuatu...">
            <button type="submit" id="ai-send" class="bg-slate-900 text-white w-10 h-10 rounded-lg flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-lg">send</span>
            </button>
        </form>
    </div>
</div>

<script>
(function () {
    const bubble = document.getElementById('ai-chat-bubble');
    const panel = document.getElementById('ai-panel');
    const closeBtn = document.getElementById('ai-close');
    const form = document.getElementById('ai-form');
    const input = document.getElementById('ai-input');
    const messages = document.getElementById('ai-messages');
    const sendBtn = document.getElementById('ai-send');

    let history = [];

    bubble.addEventListener('click', () => panel.classList.toggle('hidden'));
    closeBtn.addEventListener('click', () => panel.classList.add('hidden'));

    function addMessage(text, role) {
        const wrap = document.createElement('div');
        if (role === 'user') {
            wrap.className = 'flex justify-end';
            wrap.innerHTML = `<div class="bg-slate-900 text-white p-3 rounded-tl-xl rounded-b-xl max-w-[80%]"></div>`;
        } else {
            wrap.className = 'flex gap-2';
            wrap.innerHTML = `
                <div class="w-7 h-7 rounded-full bg-slate-900 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-sm">smart_toy</span>
                </div>
                <div class="bg-white p-3 rounded-tr-xl rounded-b-xl border border-slate-200 max-w-[80%]"></div>`;
        }
        wrap.querySelector('div:last-child').textContent = text;
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
    }

    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const text = input.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        history.push({ role: 'user', content: text });
        input.value = '';
        sendBtn.disabled = true;

        try {
            const res = await fetch("{{ route('admin.ai.chat') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ history }),
            });
            const data = await res.json();

            if (data.reply) {
                addMessage(data.reply, 'assistant');
                history.push({ role: 'assistant', content: data.reply });
            } else {
                addMessage(data.error || 'Terjadi kesalahan, coba lagi.', 'assistant');
            }
        } catch (err) {
            addMessage('Tidak bisa menghubungi AI saat ini.', 'assistant');
        }

        sendBtn.disabled = false;
    });
})();
</script>
