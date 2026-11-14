<?php
/**
 * Chatbot Widget Partial untuk Halaman Beranda
 * Sistem Informasi Pendataan Fasilitas Ruangan Kampus STMIK IKMI Cirebon
 * Widget Popup Mandiri di Pojok Kanan Bawah (Fixed: 400px x 560px, 20px dari tepi)
 */
?>
<!-- Scoped CSS Khusus Chatbot Floating Button & Popup Beranda -->
<style>
/* Floating Button Chatbot (Tunggal di Pojok Kanan Bawah) */
.chatbot-float-btn {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    width: 60px !important;
    height: 60px !important;
    border-radius: 50% !important;
    background: linear-gradient(135deg, #0d47a1 0%, #1565c0 100%) !important;
    color: #ffffff !important;
    border: 2px solid #ffffff !important;
    box-shadow: 0 8px 24px rgba(13, 71, 161, 0.35) !important;
    cursor: pointer !important;
    z-index: 1070 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s ease !important;
    outline: none !important;
    padding: 0 !important;
}

.chatbot-float-btn:hover {
    transform: translateY(-3px) scale(1.05) !important;
    box-shadow: 0 12px 30px rgba(13, 71, 161, 0.45) !important;
}

.chatbot-float-btn:active {
    transform: translateY(0) scale(0.98) !important;
}

.chatbot-float-btn.d-none {
    display: none !important;
}

.chatbot-float-badge {
    position: absolute !important;
    top: 2px !important;
    right: 2px !important;
    width: 14px !important;
    height: 14px !important;
    background-color: #22c55e !important;
    border: 2px solid #ffffff !important;
    border-radius: 50% !important;
}

#chatbot-panel.chatbot-panel {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    width: 400px !important;
    max-width: calc(100vw - 40px) !important;
    height: 560px !important;
    max-height: calc(100vh - 40px) !important;
    z-index: 1080 !important;
    margin: 0 !important;
    padding: 0 !important;
    background: #ffffff !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    border-radius: 18px !important;
    box-shadow: 0 16px 48px -4px rgba(13, 71, 161, 0.35), 0 0 0 1px rgba(13, 71, 161, 0.12) !important;
    border: none !important;
    font-family: var(--bs-font-sans-serif, system-ui, -apple-system, sans-serif) !important;
}

#chatbot-panel.chatbot-panel.d-none {
    display: none !important;
}

.chatbot-header {
    background: linear-gradient(135deg, #0d47a1 0%, #1565c0 100%) !important;
    border-bottom: 2px solid #ffc107 !important;
    flex-shrink: 0 !important;
}

.chatbot-messages {
    flex: 1 1 auto !important;
    min-height: 0 !important;
    max-height: 100% !important;
    background-color: #f8fafc !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    scroll-behavior: smooth !important;
}

.chatbot-footer {
    flex-shrink: 0 !important;
    background: #ffffff !important;
    border-top: 1px solid #e2e8f0 !important;
}

@keyframes slideUpPopup {
    from {
        opacity: 0;
        transform: translateY(24px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.animate-slide-up {
    animation: slideUpPopup 0.28s cubic-bezier(0.16, 1, 0.3, 1) forwards !important;
}

/* Responsif Mobile */
@media (max-width: 576px) {
    .chatbot-float-btn {
        bottom: 16px !important;
        right: 16px !important;
        width: 54px !important;
        height: 54px !important;
    }
    #chatbot-panel.chatbot-panel {
        bottom: 12px !important;
        right: 12px !important;
        left: 12px !important;
        width: calc(100vw - 24px) !important;
        max-width: calc(100vw - 24px) !important;
        height: calc(100vh - 24px) !important;
        max-height: 560px !important;
        border-radius: 16px !important;
    }
}
</style>

<!-- Floating Button Chatbot (Tunggal di Pojok Kanan Bawah Beranda) -->
<button type="button" id="chatbot-toggle-btn" class="chatbot-float-btn shadow-lg" aria-label="Buka Asisten Fasilitas IKMI" title="Buka Asisten Fasilitas IKMI">
    <i class="bi bi-robot fs-3 text-white"></i>
    <span class="chatbot-float-badge" title="Online"></span>
</button>

<!-- Metadata Sesi Server-Side Valid (Hanya terisi jika pengguna terotentikasi) -->
<div id="chatbot-session-meta" data-session-id="<?= Auth::check() ? htmlspecialchars(session_id()) : '' ?>" style="display:none;" aria-hidden="true"></div>

<!-- Jendela Popup Percakapan Chatbot (Pojok Kanan Bawah, Default Tertutup) -->
<div id="chatbot-panel" class="chatbot-panel card shadow-lg d-none" role="dialog" aria-modal="true" aria-labelledby="chatbot-title">
    <!-- Header Panel: Biru Resmi dengan Nama Asisten Fasilitas IKMI -->
    <div class="chatbot-header text-white p-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="chatbot-avatar bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 36px; height: 36px;">
                <i class="bi bi-robot fs-5"></i>
            </div>
            <div>
                <h6 id="chatbot-title" class="mb-0 fw-bold text-white fs-6">Asisten Fasilitas IKMI</h6>
                <small class="text-white-50 d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                    <span class="badge-status-dot" style="display:inline-block; width:8px; height:8px; background-color:#22c55e; border-radius:50%;"></span> STMIK IKMI Cirebon
                </small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-1">
            <button type="button" id="chatbot-refresh-btn" class="btn btn-sm btn-link text-white-50 text-decoration-none p-1" title="Bersihkan Percakapan" aria-label="Bersihkan Percakapan">
                <i class="bi bi-arrow-clockwise fs-6"></i>
            </button>
            <button type="button" id="chatbot-close-btn" class="btn btn-sm btn-link text-white text-decoration-none p-1" aria-label="Tutup Panel Chatbot" title="Tutup Chatbox (X)">
                <i class="bi bi-x-lg fs-5"></i>
            </button>
        </div>
    </div>

    <!-- Bar Opsi Sederhana di Atas Pesan -->
    <div class="px-3 py-1.5 bg-light border-bottom d-flex align-items-center justify-content-between text-muted" style="font-size: 0.73rem; flex-shrink: 0;">
        <div class="form-check m-0 d-flex align-items-center gap-1.5">
            <input class="form-check-input cursor-pointer mt-0" type="checkbox" id="chk-grounding-db" checked>
            <label class="form-check-label user-select-none cursor-pointer fw-semibold text-secondary" for="chk-grounding-db">
                <i class="bi bi-database-check text-success"></i> Data Aktual DB
            </label>
        </div>
        <div class="form-check m-0 d-flex align-items-center gap-1.5">
            <input class="form-check-input cursor-pointer mt-0" type="checkbox" id="chk-auto-scroll" checked>
            <label class="form-check-label user-select-none cursor-pointer text-muted" for="chk-auto-scroll">
                Auto-scroll
            </label>
        </div>
    </div>

    <!-- Area Percakapan (Scroll Mandiri) -->
    <div id="chatbot-messages" class="chatbot-messages p-3 d-flex flex-column gap-3" tabindex="0">
        <!-- Pesan Sambutan Singkat Bot -->
        <div class="chat-message bot-message d-flex gap-2">
            <div class="message-avatar flex-shrink-0">
                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px; font-size: 0.85rem;">
                    <i class="bi bi-robot"></i>
                </div>
            </div>
            <div class="message-content bg-light p-3 rounded-4 border shadow-xs" style="max-width: 88%;">
                <p class="mb-1 text-dark fw-semibold small">Asisten Fasilitas IKMI</p>
                <div class="message-body text-secondary small">
                    Halo! Saya adalah <strong>Asisten Fasilitas IKMI</strong> STMIK IKMI Cirebon. Saya siap membantu Anda memperoleh informasi seputar sarana, prasarana, gedung, dan ruangan kampus.
                </div>
            </div>
        </div>

        <!-- Kontainer 5 Pertanyaan Cepat Resmi -->
        <div id="quick-prompts-container" class="quick-prompts-wrapper p-2 bg-light bg-opacity-75 rounded-3 border border-dashed">
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
        </div>

        <!-- Indikator Loading Respons -->
        <div id="chatbot-loading" class="chat-message bot-message d-none d-flex gap-2 align-items-center">
            <div class="message-avatar flex-shrink-0">
                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                    <i class="bi bi-robot"></i>
                </div>
            </div>
            <div class="message-content bg-light px-3 py-2 rounded-4 border d-inline-flex align-items-center gap-2">
                <span class="spinner-grow spinner-grow-sm text-primary" role="status" aria-hidden="true"></span>
                <span class="small text-muted fst-italic">Asisten sedang mencari data...</span>
            </div>
        </div>
    </div>

    <!-- Form Input Pertanyaan & Tombol Kirim di Bagian Bawah -->
    <div class="chatbot-footer p-3 bg-white border-top">
        <form id="chatbot-form" class="m-0" autocomplete="off">
            <div class="input-group">
                <input 
                    type="text" 
                    id="chatbot-input" 
                    class="form-control rounded-start-pill ps-3 border-end-0" 
                    placeholder="Ketik pertanyaan fasilitas kampus..." 
                    maxlength="500" 
                    aria-label="Pesan untuk chatbot fasilitas"
                    required
                >
                <button class="btn btn-primary rounded-end-pill px-3 shadow-none" type="submit" id="chatbot-send-btn" title="Kirim Pertanyaan">
                    <i class="bi bi-send-fill text-warning"></i>
                    <span class="visually-hidden">Kirim</span>
                </button>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2 px-1 text-muted" style="font-size: 0.72rem;">
                <span><i class="bi bi-shield-check text-success"></i> Terintegrasi data kampus STMIK IKMI</span>
                <span id="chatbot-char-count">0/500</span>
            </div>
        </form>
    </div>
</div>
