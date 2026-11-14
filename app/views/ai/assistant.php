<div class="container-fluid p-0">
    <!-- Header Modul AI Assistant -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 bg-primary text-white p-3 d-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px;">
                        <i class="bi bi-robot fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h4 class="fw-bold mb-0 text-dark">AI Assistant Sarpras</h4>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small">
                                <i class="bi bi-shield-check me-1"></i> Data Terverifikasi Database
                            </span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">
                            Pusat konsultasi cerdas berbasis bahasa natural untuk penelusuran inventaris, status kelaikan fisik, dan prioritas perbaikan kampus STMIK IKMI Cirebon.
                        </p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php if (Auth::hasRole('admin')): ?>
                    <a href="<?= base_url('ai/pengaturan') ?>" class="btn btn-warning rounded-pill btn-sm px-3 fw-bold shadow-sm">
                        <i class="bi bi-sliders2-vertical me-1"></i> Pengaturan Model AI & Key
                    </a>
                    <?php endif; ?>
                    <a href="<?= base_url('ai') ?>" class="btn btn-outline-secondary rounded-pill btn-sm px-3">
                        <i class="bi bi-clock-history me-1"></i> Log Audit AI
                    </a>
                    <button type="button" class="btn btn-outline-danger rounded-pill btn-sm px-3" id="btnClearChat">
                        <i class="bi bi-trash3 me-1"></i> Bersihkan Obrolan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Utama: Chat Interface -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden d-flex flex-column" style="height: 680px;">
                <!-- Header Chat Box -->
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="spinner-grow spinner-grow-sm text-success" role="status" style="width: 10px; height: 10px;"></div>
                        <span class="fw-bold small text-dark">Sesi Interaktif Aktif</span>
                        <span class="text-muted small">&bull; Provider: <strong><?= htmlspecialchars($provider_name) ?></strong></span>
                        <?php if (Auth::hasRole('admin')): ?>
                        <a href="<?= base_url('ai/pengaturan') ?>" class="badge bg-primary-subtle text-primary border border-primary text-decoration-none ms-1" title="Klik untuk ubah model / API key">
                            <i class="bi bi-gear-fill me-1"></i> Ubah Model
                        </a>
                        <?php endif; ?>
                    </div>
                    <span class="badge bg-light text-muted border small">
                        <i class="bi bi-lock-fill me-1 text-primary"></i> Zero Arbitrary SQL
                    </span>
                </div>

                <!-- Kontainer Pesan Percakapan (Scrollable) -->
                <div class="card-body p-4 overflow-y-auto flex-grow-1 bg-body-tertiary" id="chatMessageContainer" style="scroll-behavior: smooth;">
                    <!-- Pesan Sambutan Awal dari AI -->
                    <div class="d-flex gap-3 mb-4">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="flex-grow-1" style="max-width: 85%;">
                            <div class="bg-white p-3 rounded-4 shadow-sm border border-light text-dark">
                                <div class="fw-bold text-primary small mb-1 d-flex align-items-center gap-1 flex-wrap">
                                    <span>Asisten Sarpras IKMI</span>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 0.65rem;">Sistem Faktual</span>
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill" style="font-size: 0.65rem;">
                                        <i class="bi bi-cpu me-1"></i><?= htmlspecialchars($provider_name) ?>
                                    </span>
                                </div>
                                <p class="mb-2 small">
                                    Halo, <strong><?= htmlspecialchars($user['nama_lengkap']) ?></strong>! Saya siap membantu Anda menelusuri data aset dan inventaris kampus STMIK IKMI Cirebon. Seluruh jawaban saya diproses langsung dari database real-time tanpa halusinasi data.
                                </p>
                                <div class="p-2 bg-light rounded-3 small text-muted border border-dashed">
                                    <i class="bi bi-lightbulb-fill text-warning me-1"></i> <strong>Tip:</strong> Anda bisa langsung mengeklik salah satu pertanyaan di bawah atau mengetikkan pertanyaan sendiri di kolom input.
                                </div>
                            </div>
                            <small class="text-muted ps-2" style="font-size: 0.72rem;">Hari ini</small>
                        </div>
                    </div>

                    <!-- Riwayat Pesan Sebelumnya (Jika Ada) -->
                    <?php if (!empty($chat_history)): ?>
                        <?php foreach ($chat_history as $msg): ?>
                            <!-- Pesan Pengguna -->
                            <div class="d-flex gap-3 mb-4 justify-content-end">
                                <div class="text-end" style="max-width: 85%;">
                                    <div class="bg-primary text-white p-3 rounded-4 shadow-sm text-start">
                                        <div class="small fw-semibold text-white-50 mb-1">Anda</div>
                                        <div class="small"><?= nl2br(htmlspecialchars($msg['question'])) ?></div>
                                    </div>
                                    <small class="text-muted pe-2" style="font-size: 0.72rem;">
                                        <?= date('d M Y H:i', strtotime($msg['created_at'])) ?>
                                    </small>
                                </div>
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                                </div>
                            </div>

                            <!-- Pesan Jawaban AI -->
                            <div class="d-flex gap-3 mb-4">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="bi bi-robot"></i>
                                </div>
                                <div class="flex-grow-1" style="max-width: 85%;">
                                    <div class="bg-white p-3 rounded-4 shadow-sm border border-light text-dark">
                                        <div class="fw-bold text-primary small mb-1">
                                            Asisten Sarpras IKMI
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-2" style="font-size: 0.65rem;">
                                                <i class="bi bi-check-circle me-1"></i>Terverifikasi (<?= $msg['waktu_eksekusi_ms'] ?>ms)
                                            </span>
                                        </div>
                                        <div class="small ai-rendered-content text-secondary">
                                            <?= nl2br(htmlspecialchars($msg['response'])) ?>
                                        </div>
                                    </div>
                                    <small class="text-muted ps-2" style="font-size: 0.72rem;">
                                        <?= date('d M Y H:i', strtotime($msg['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Loading Indicator (Disembunyikan secara default) -->
                    <div id="aiLoadingIndicator" class="d-none">
                        <div class="d-flex gap-3 mb-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                                <i class="bi bi-robot"></i>
                            </div>
                            <div class="bg-white p-3 rounded-4 shadow-sm border border-light text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                    <span class="small text-muted fw-semibold">Memverifikasi data dan menyusun respons faktual...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Prompts Chips (Pertanyaan Rekomendasi) -->
                <div class="p-3 bg-white border-top border-bottom overflow-x-auto text-nowrap" id="quickPromptBar">
                    <span class="small text-muted fw-semibold me-2"><i class="bi bi-lightning-charge-fill text-warning"></i> Pertanyaan Cepat:</span>
                    <?php foreach ($quick_prompts as $qp): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 me-1 mb-1 quick-prompt-btn" style="font-size: 0.78rem;" data-prompt="<?= htmlspecialchars($qp) ?>">
                            <?= htmlspecialchars($qp) ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Input Chat Bar -->
                <div class="card-footer bg-white p-3">
                    <form id="aiChatForm" class="d-flex align-items-center gap-2">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                                <i class="bi bi-chat-dots"></i>
                            </span>
                            <input type="text" class="form-control bg-light border-start-0 border-end-0 py-2 shadow-none small" 
                                   id="inputQuestion" 
                                   placeholder="Ketik pertanyaan fasilitas... (misal: Berapa jumlah fasilitas yang rusak berat?)" 
                                   autocomplete="off" required>
                            <button type="button" class="btn btn-light border border-start-0 text-muted px-2" id="btnClearInput" title="Bersihkan input">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm" id="btnSubmitChat">
                            <i class="bi bi-send-fill"></i>
                            <span>Kirim</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Samping: Arsitektur Keamanan & Bantuan -->
        <div class="col-lg-4">
            <!-- Card 1: Alur Kerja & Keamanan Grounding -->
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-shield-lock-fill text-primary me-2"></i>Keamanan & Grounding
                    </h6>
                </div>
                <div class="card-body p-4 small">
                    <p class="text-muted mb-3">
                        Sistem mengimplementasikan arsitektur proteksi bertingkat untuk memastikan akurasi data 100%:
                    </p>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="p-2 rounded-3 bg-light border-start border-3 border-primary">
                            <strong>1. Natural Language Intent</strong>
                            <div class="text-muted">Menganalisis maksud pertanyaan & mengekstrak entitas.</div>
                        </div>
                        <div class="p-2 rounded-3 bg-light border-start border-3 border-info">
                            <strong>2. Whitelist Query Validation</strong>
                            <div class="text-muted">Hanya fungsi query terdaftar yang diizinkan (Anti Arbitrary SQL).</div>
                        </div>
                        <div class="p-2 rounded-3 bg-light border-start border-3 border-warning">
                            <strong>3. Real-Time DB Extraction</strong>
                            <div class="text-muted">Mengambil data faktual real-time dari MariaDB/MySQL.</div>
                        </div>
                        <div class="p-2 rounded-3 bg-light border-start border-3 border-success">
                            <strong>4. Synthesized Factual Answer</strong>
                            <div class="text-muted">Menyusun jawaban bahasa natural berdasarkan data terverifikasi.</div>
                        </div>
                    </div>
                    <div class="alert alert-success py-2 px-3 rounded-3 small mb-0">
                        <i class="bi bi-check-circle-fill me-1"></i> Bebas halusinasi & aman dari manipulasi query SQL.
                    </div>
                </div>
            </div>

            <!-- Card 2: Panduan Topik Pertanyaan yang Didukung -->
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="bi bi-question-circle-fill text-warning me-2"></i>Topik yang Dapat Ditanyakan
                    </h6>
                </div>
                <div class="card-body p-4 small">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item px-0 py-2">
                            <strong class="text-dark d-block mb-1"><i class="bi bi-activity text-danger me-1"></i> Kondisi Fisik Fasilitas</strong>
                            <span class="text-muted">Contoh: "Berapa fasilitas yang rusak berat?", "Fasilitas rusak ringan"</span>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <strong class="text-dark d-block mb-1"><i class="bi bi-building text-primary me-1"></i> Sebaran Fasilitas Gedung</strong>
                            <span class="text-muted">Contoh: "Berapa fasilitas di Gedung A?", "Fasilitas di Gedung B"</span>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <strong class="text-dark d-block mb-1"><i class="bi bi-door-open text-info me-1"></i> Fasilitas Ruangan Spesifik</strong>
                            <span class="text-muted">Contoh: "Tampilkan fasilitas di Lab AI", "Ruang mana paling lengkap?"</span>
                        </div>
                        <div class="list-group-item px-0 py-2">
                            <strong class="text-dark d-block mb-1"><i class="bi bi-tools text-warning me-1"></i> Prioritas & Pemeliharaan</strong>
                            <span class="text-muted">Contoh: "Fasilitas apa yang harus diprioritaskan?", "Rekomendasi servis"</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatContainer = document.getElementById('chatMessageContainer');
    const chatForm = document.getElementById('aiChatForm');
    const inputQuestion = document.getElementById('inputQuestion');
    const btnSubmit = document.getElementById('btnSubmitChat');
    const loadingIndicator = document.getElementById('aiLoadingIndicator');
    const btnClearInput = document.getElementById('btnClearInput');
    const btnClearChat = document.getElementById('btnClearChat');
    const quickButtons = document.querySelectorAll('.quick-prompt-btn');

    // Auto scroll ke pesan paling bawah saat pertama load
    scrollChatToBottom();

    // Event listener quick prompt buttons
    quickButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const promptText = this.getAttribute('data-prompt');
            inputQuestion.value = promptText;
            submitQuestion(promptText);
        });
    });

    // Clear input button
    btnClearInput.addEventListener('click', function() {
        inputQuestion.value = '';
        inputQuestion.focus();
    });

    // Clear chat history
    btnClearChat.addEventListener('click', function() {
        if (confirm('Apakah Anda yakin ingin membersihkan riwayat obrolan AI?')) {
            window.location.href = '<?= base_url('ai-assistant/clearHistory') ?>';
        }
    });

    // Form submit listener
    chatForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const question = inputQuestion.value.trim();
        if (question) {
            submitQuestion(question);
        }
    });

    // Fungsi kirim pertanyaan via AJAX
    function submitQuestion(question) {
        // Render bubble pesan user di UI
        appendUserMessage(question);
        inputQuestion.value = '';

        // Tampilkan loading indicator
        loadingIndicator.classList.remove('d-none');
        btnSubmit.disabled = true;
        scrollChatToBottom();

        // AJAX POST ke endpoint /ai-assistant/ask
        fetch('<?= base_url('ai-assistant/ask') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ question: question })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Gagal memproses permintaan ke server.');
            }
            return response.json();
        })
        .then(data => {
            loadingIndicator.classList.add('d-none');
            btnSubmit.disabled = false;

            if (data.status === 'success') {
                appendAiMessage(data.response, data.execution_time_ms, data.timestamp, false, data.provider);
            } else {
                appendAiMessage(data.message || data.response || 'Terjadi kendala saat memproses jawaban.', 0, 'Baru saja', true);
            }
            inputQuestion.focus();
        })
        .catch(err => {
            loadingIndicator.classList.add('d-none');
            btnSubmit.disabled = false;
            appendAiMessage('Maaf, koneksi atau sistem sedang sibuk. Silakan coba sesaat lagi.', 0, 'Baru saja', true);
            inputQuestion.focus();
        });
    }

    function appendUserMessage(text) {
        const timeNow = getCurrentTimeString();
        const userHtml = `
            <div class="d-flex gap-3 mb-4 justify-content-end">
                <div class="text-end" style="max-width: 85%;">
                    <div class="bg-primary text-white p-3 rounded-4 shadow-sm text-start">
                        <div class="small fw-semibold text-white-50 mb-1">Anda</div>
                        <div class="small">${escapeHtml(text)}</div>
                    </div>
                    <small class="text-muted pe-2" style="font-size: 0.72rem;">${timeNow}</small>
                </div>
                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                    <?= strtoupper(substr($user['nama_lengkap'], 0, 1)) ?>
                </div>
            </div>
        `;
        // Sisipkan sebelum loading indicator
        loadingIndicator.insertAdjacentHTML('beforebegin', userHtml);
        scrollChatToBottom();
    }

    const activeProviderName = <?= json_encode($provider_name) ?>;

    function appendAiMessage(text, executionTimeMs, timestamp, isError = false, provider = '') {
        const timeLabel = timestamp || getCurrentTimeString();
        const badgeClass = isError ? 'bg-danger-subtle text-danger border-danger-subtle' : 'bg-success-subtle text-success border-success-subtle';
        const badgeText = isError ? 'Error' : `Terverifikasi (${executionTimeMs}ms)`;
        const formattedText = formatMarkdownToHtml(text);
        const provLabel = provider || activeProviderName;

        const aiHtml = `
            <div class="d-flex gap-3 mb-4">
                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 40px; height: 40px;">
                    <i class="bi bi-robot"></i>
                </div>
                <div class="flex-grow-1" style="max-width: 85%;">
                    <div class="bg-white p-3 rounded-4 shadow-sm border border-light text-dark">
                        <div class="fw-bold text-primary small mb-1 d-flex align-items-center gap-1 flex-wrap">
                            <span>Asisten Sarpras IKMI</span>
                            <span class="badge ${badgeClass} border rounded-pill" style="font-size: 0.65rem;">
                                <i class="bi bi-check-circle me-1"></i>${badgeText}
                            </span>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill" style="font-size: 0.65rem;">
                                <i class="bi bi-cpu me-1"></i>${escapeHtml(provLabel)}
                            </span>
                        </div>
                        <div class="small ai-rendered-content text-secondary">
                            ${formattedText}
                        </div>
                    </div>
                    <small class="text-muted ps-2" style="font-size: 0.72rem;">${timeLabel}</small>
                </div>
            </div>
        `;
        loadingIndicator.insertAdjacentHTML('beforebegin', aiHtml);
        scrollChatToBottom();
    }

    function scrollChatToBottom() {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function getCurrentTimeString() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Formatter sederhana untuk mengubah Markdown dasar (**bold**, *italic*, `code`, \n) menjadi HTML
    function formatMarkdownToHtml(text) {
        let escaped = escapeHtml(text);
        // Bold **text**
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic *text*
        escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Code `code`
        escaped = escaped.replace(/`(.*?)`/g, '<code class="bg-light text-primary px-1 rounded">$1</code>');
        // Newlines
        escaped = escaped.replace(/\n/g, '<br>');
        return escaped;
    }
});
</script>
