/**
 * Chatbot Client JavaScript
 * Sistem Informasi Pendataan Fasilitas Ruangan Kampus STMIK IKMI Cirebon
 * Menghubungkan UI Beranda dengan Backend Chatbot Service (Node.js Port 3001)
 * Mode: Floating Button Pojok Kanan Bawah dengan Popup Toggle Interaktif
 *
 * v2.1 — Menambahkan chatSessionId untuk konteks percakapan multi-turn
 */

document.addEventListener('DOMContentLoaded', () => {
    const CHATBOT_API_URL = 'http://localhost:3001/api/chat';

    // DOM Elements
    const toggleBtn = document.getElementById('chatbot-toggle-btn');
    const panel = document.getElementById('chatbot-panel');
    const closeBtn = document.getElementById('chatbot-close-btn');
    const refreshBtn = document.getElementById('chatbot-refresh-btn');
    const messagesContainer = document.getElementById('chatbot-messages');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const sendBtn = document.getElementById('chatbot-send-btn');
    const loadingElem = document.getElementById('chatbot-loading');
    const charCount = document.getElementById('chatbot-char-count');
    const chkAutoScroll = document.getElementById('chk-auto-scroll');

    if (!panel || !form || !input) {
        return; // Widget tidak ditemukan di halaman ini
    }

    let isSubmitting = false;

    // Buat atau ambil chatSessionId unik per tab/sesi halaman
    // Prioritas: gunakan PHPSESSID dari meta tag jika tersedia, lalu fallback ke sessionStorage
    function getChatSessionId() {
        const metaSession = document.getElementById('chatbot-session-meta');
        const phpSessionId = metaSession ? (metaSession.getAttribute('data-session-id') || '').trim() : '';
        if (phpSessionId) return phpSessionId;

        // Fallback: buat ID unik per tab browser dan simpan di sessionStorage
        let sid = sessionStorage.getItem('chatbot_session_id');
        if (!sid) {
            sid = 'anon-' + Date.now() + '-' + Math.random().toString(36).substring(2, 10);
            sessionStorage.setItem('chatbot_session_id', sid);
        }
        return sid;
    }

    // 1. Fungsi Buka Popup Chatbot
    function openChatbot() {
        panel.classList.remove('d-none');
        panel.classList.add('animate-slide-up');
        if (toggleBtn) {
            toggleBtn.classList.add('d-none');
            toggleBtn.setAttribute('aria-expanded', 'true');
        }
        setTimeout(() => {
            input.focus();
            scrollToBottom();
        }, 120);
    }

    // 2. Fungsi Tutup Popup Chatbot
    function closeChatbot() {
        panel.classList.add('d-none');
        panel.classList.remove('animate-slide-up');
        if (toggleBtn) {
            toggleBtn.classList.remove('d-none');
            toggleBtn.setAttribute('aria-expanded', 'false');
            toggleBtn.focus();
        }
        // Simpan status ditutup selama sesi halaman aktif
        sessionStorage.setItem('chatbot_closed_session', 'true');
    }

    // 3. Status Default Saat Halaman Dimuat: Popup Tertutup, Floating Button Tampil
    panel.classList.add('d-none');
    if (toggleBtn) {
        toggleBtn.classList.remove('d-none');
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            openChatbot();
        });
    }

    // 4. Tombol Tutup (X) Pada Header Chatbot
    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            closeChatbot();
        });
    }

    // 5. Dukungan Tombol ESC Keyboard Untuk Menutup Popup
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.classList.contains('d-none')) {
            closeChatbot();
        }
    });

    // 5. Character Counter Input
    input.addEventListener('input', () => {
        const len = input.value.length;
        if (charCount) {
            charCount.textContent = `${len}/500`;
            if (len >= 480) {
                charCount.classList.add('text-danger');
            } else {
                charCount.classList.remove('text-danger');
            }
        }
    });

    // 6. Reset Chatbot Messages (Tombol Refresh Header)
    if (refreshBtn) {
        refreshBtn.addEventListener('click', () => {
            const initialGreeting = `
                <div class="chat-message bot-message d-flex gap-2">
                    <div class="message-avatar flex-shrink-0">
                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-robot"></i>
                        </div>
                    </div>
                    <div class="message-content bg-light p-3 rounded-4 border shadow-xs">
                        <p class="mb-1 text-dark fw-semibold small">Asisten Fasilitas IKMI</p>
                        <div class="message-body text-secondary small">
                            Percakapan telah direset. Silakan tanyakan informasi mengenai sarana, prasarana, gedung, dan ruangan kampus STMIK IKMI Cirebon.
                        </div>
                    </div>
                </div>
            `;
            messagesContainer.innerHTML = initialGreeting;

            // Kembalikan kontainer 5 pertanyaan cepat resmi
            const wrapper = document.createElement('div');
            wrapper.id = 'quick-prompts-container';
            wrapper.className = 'quick-prompts-wrapper p-2 bg-light bg-opacity-75 rounded-3 border border-dashed';
            wrapper.innerHTML = `
                <div class="small fw-bold text-muted mb-2 d-flex align-items-center gap-1">
                    <i class="bi bi-lightning-charge-fill text-warning"></i> Pertanyaan Cepat:
                </div>
                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-outline-primary btn-sm text-start py-2 px-3 rounded-pill quick-prompt-btn" data-question="Siapa Anda?">
                        <i class="bi bi-question-circle me-1 text-primary"></i> 1. Siapa Anda?
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm text-start py-2 px-3 rounded-pill quick-prompt-btn" data-question="Buatkan coding Python dengan menuliskan nama Anda.">
                        <i class="bi bi-shield-slash-fill me-1 text-danger"></i> 2. Buatkan coding Python dengan menuliskan nama Anda.
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm text-start py-2 px-3 rounded-pill quick-prompt-btn" data-question="Siapa saya?">
                        <i class="bi bi-person-bounding-box me-1 text-warning"></i> 3. Siapa saya?
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm text-start py-2 px-3 rounded-pill quick-prompt-btn" data-question="Ada berapa total fasilitas yang terdata?">
                        <i class="bi bi-boxes me-1 text-info"></i> 4. Ada berapa total fasilitas yang terdata?
                    </button>
                    <button type="button" class="btn btn-outline-primary btn-sm text-start py-2 px-3 rounded-pill quick-prompt-btn" data-question="Ada berapa jumlah ruangan di kampus?">
                        <i class="bi bi-door-open-fill me-1 text-success"></i> 5. Ada berapa jumlah ruangan di kampus?
                    </button>
                </div>
            `;
            messagesContainer.appendChild(wrapper);
            rebindQuickPrompts();

            // Kembalikan indikator loading
            messagesContainer.appendChild(loadingElem);
            scrollToBottom();
        });
    }

    // 7. Bind Pertanyaan Cepat
    function rebindQuickPrompts() {
        document.querySelectorAll('.quick-prompt-btn').forEach(btn => {
            btn.onclick = (e) => {
                e.preventDefault();
                const questionText = btn.getAttribute('data-question');
                if (questionText && !isSubmitting) {
                    input.value = questionText;
                    if (charCount) charCount.textContent = `${questionText.length}/500`;
                    sendMessage(questionText);
                }
            };
        });
    }
    rebindQuickPrompts();

    // 8. Scroll Percakapan ke Bawah
    function scrollToBottom() {
        if (!chkAutoScroll || chkAutoScroll.checked) {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
    }

    // 9. Sanitasi HTML
    function escapeHtml(string) {
        const entityMap = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;',
            '/': '&#x2F;'
        };
        return String(string).replace(/[&<>"'\/]/g, s => entityMap[s]);
    }

    // 10. Format Markdown Respons Bot
    function formatMessageContent(rawText) {
        if (!rawText) return '';

        // Tangani blok kode jika ada
        const codeBlockRegex = /```([a-zA-Z0-9_-]*)\n([\s\S]*?)```/g;
        let formatted = rawText.replace(codeBlockRegex, (match, lang, code) => {
            const escapedCode = escapeHtml(code.trim());
            const langLabel = lang ? lang.toUpperCase() : 'CODE';
            return `
                <div class="code-snippet-box my-2 rounded-3 overflow-hidden border border-secondary border-opacity-25 shadow-xs">
                    <div class="d-flex justify-content-between align-items-center bg-dark text-white px-3 py-1 text-uppercase small" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                        <span><i class="bi bi-file-earmark-code me-1 text-warning"></i> ${langLabel}</span>
                        <button type="button" class="btn btn-sm btn-link text-white-50 text-decoration-none p-0 copy-code-btn" onclick="navigator.clipboard.writeText(this.closest('.code-snippet-box').querySelector('code').innerText); this.innerText='Tersalin!'; setTimeout(()=>this.innerText='Salin', 2000)">
                            Salin
                        </button>
                    </div>
                    <pre class="bg-dark text-warning p-3 m-0 font-monospace small overflow-x-auto mb-0" style="font-size: 0.85rem; line-height: 1.5;"><code>${escapedCode}</code></pre>
                </div>
            `;
        });

        // Inline code
        formatted = formatted.replace(/`([^`]+)`/g, (match, code) => {
            return `<code class="bg-secondary bg-opacity-10 text-dark px-1.5 py-0.5 rounded font-monospace small">${escapeHtml(code)}</code>`;
        });

        // Bold
        formatted = formatted.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');

        // Italic
        formatted = formatted.replace(/\*([^*]+)\*/g, '<em>$1</em>');

        // Line breaks
        formatted = formatted.replace(/\n/g, '<br>');

        return formatted;
    }

    // 11. Render Pesan Pengguna
    function appendUserMessage(text) {
        const timeNow = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-message user-message d-flex gap-2 justify-content-end';
        msgDiv.innerHTML = `
            <div class="message-content bg-primary text-white p-3 rounded-4 shadow-sm" style="max-width: 85%;">
                <div class="message-body small">${escapeHtml(text)}</div>
                <div class="text-end text-white-50 mt-1" style="font-size: 0.68rem;">${timeNow}</div>
            </div>
            <div class="message-avatar flex-shrink-0">
                <div class="avatar-sm bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-person-fill"></i>
                </div>
            </div>
        `;
        messagesContainer.insertBefore(msgDiv, loadingElem);
        scrollToBottom();
    }

    // 12. Render Pesan Asisten Bot
    function appendBotMessage(rawText, source = 'local') {
        const timeNow = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const msgDiv = document.createElement('div');
        msgDiv.className = 'chat-message bot-message d-flex gap-2';

        let badgeSource = '';
        if (source === 'database_actual') {
            badgeSource = '<span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.65rem;"><i class="bi bi-database-check me-0.5"></i> Data Aktual</span>';
        } else if (source === 'security_policy') {
            badgeSource = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-1" style="font-size: 0.65rem;"><i class="bi bi-shield-x me-0.5"></i> Kebijakan Sistem</span>';
        }

        const formattedHtml = formatMessageContent(rawText);

        msgDiv.innerHTML = `
            <div class="message-avatar flex-shrink-0">
                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                    <i class="bi bi-robot"></i>
                </div>
            </div>
            <div class="message-content bg-light p-3 rounded-4 border shadow-xs" style="max-width: 88%;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-dark fw-semibold small">Asisten Fasilitas IKMI</span>
                    ${badgeSource}
                </div>
                <div class="message-body text-secondary small">${formattedHtml}</div>
                <div class="text-end text-muted mt-1" style="font-size: 0.68rem;">${timeNow}</div>
            </div>
        `;
        messagesContainer.insertBefore(msgDiv, loadingElem);
        scrollToBottom();
    }

    // 13. Kirim Pesan ke API Chatbot Backend Node.js Port 3001
    async function sendMessage(questionText) {
        const text = (questionText || input.value || '').trim();
        if (!text || isSubmitting) return;

        isSubmitting = true;
        sendBtn.disabled = true;
        input.disabled = true;
        input.value = '';
        if (charCount) charCount.textContent = '0/500';

        appendUserMessage(text);

        // Tampilkan loading indicator
        loadingElem.classList.remove('d-none');
        scrollToBottom();

        try {
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 15000); // 15 detik timeout

            // Ambil session ID untuk autentikasi server-side & konteks percakapan multi-turn
            const chatSessionId = getChatSessionId();
            const metaSession = document.getElementById('chatbot-session-meta');
            const phpSessionId = metaSession ? (metaSession.getAttribute('data-session-id') || '') : '';

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };
            if (phpSessionId) {
                headers['X-Session-ID'] = phpSessionId;
            }

            const response = await fetch(CHATBOT_API_URL, {
                method: 'POST',
                credentials: 'include',
                headers: headers,
                body: JSON.stringify({
                    message: text,
                    sessionId: phpSessionId,       // untuk resolveUserSession (autentikasi)
                    chatSessionId: chatSessionId    // untuk riwayat percakapan multi-turn
                }),
                signal: controller.signal
            });

            clearTimeout(timeoutId);

            if (!response.ok) {
                const errData = await response.json().catch(() => ({}));
                throw new Error(errData.message || `HTTP ${response.status}: Layanan tidak dapat merespons`);
            }

            const data = await response.json();
            loadingElem.classList.add('d-none');

            if (data.status === 'success' && data.response) {
                appendBotMessage(data.response, data.source);
            } else {
                appendBotMessage('Maaf, saya tidak dapat memproses jawaban saat ini. Silakan ulangi pertanyaan Anda.');
            }

        } catch (error) {
            loadingElem.classList.add('d-none');
            console.error('[Chatbot Fetch Error]', error);

            let errorNotice = 'Terjadi kendala saat menghubungi server chatbot.';
            if (error.name === 'AbortError') {
                errorNotice = 'Waktu permintaan habis (timeout). Silakan periksa jaringan dan coba kembali.';
            } else if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                errorNotice = 'Layanan backend chatbot (Node.js port 3001) belum aktif. Pastikan service telah dijalankan dengan perintah `npm start` di folder `chatbot-service`.';
            } else {
                errorNotice = `Gagal: ${error.message}`;
            }

            appendBotMessage(`⚠️ ${errorNotice}`);
        } finally {
            isSubmitting = false;
            sendBtn.disabled = false;
            input.disabled = false;
            input.focus();
            scrollToBottom();
        }
    }

    // 14. Event Form Submit
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage();
    });
});
