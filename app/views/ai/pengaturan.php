<div class="container-fluid p-0">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <span class="badge bg-warning text-dark px-3 py-1 rounded-pill small mb-2 fw-bold">
                    <i class="bi bi-shield-lock-fill me-1"></i> Khusus Administrator Sistem
                </span>
                <h4 class="fw-bold mb-1">Konfigurasi AI Assistant & Multi-Model</h4>
                <p class="text-muted small mb-0">
                    Atur provider kecerdasan buatan, API Key, dan model AI secara dinamis tanpa perlu mengedit file <code>.env</code> manual.
                </p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= base_url('ai-assistant') ?>" class="btn btn-outline-primary rounded-pill px-3 fw-semibold">
                    <i class="bi bi-robot me-1"></i> Buka Chatbot Assistant
                </a>
                <a href="<?= base_url('ai') ?>" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold">
                    <i class="bi bi-cpu me-1"></i> Log Analitik AI
                </a>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <?php if (!empty($flash_success)): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Konfigurasi Berhasil Disimpan</h6>
                    <small><?= htmlspecialchars($flash_success) ?></small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash_error)): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                <div>
                    <h6 class="mb-0 fw-bold">Perhatian</h6>
                    <small><?= htmlspecialchars($flash_error) ?></small>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Overview Active Engine Card -->
    <?php
        $currProv = $current_config['provider'] ?? 'gemini';
        $currModel = $current_config['model'] ?? 'gemini-1.5-flash';
        $currKey = $current_config['api_key'] ?? '';
        $maskedKey = !empty($currKey) ? substr($currKey, 0, 6) . '••••••••' . substr($currKey, -4) : '(Belum Dikonfigurasi)';
        $provData = $providers[$currProv] ?? null;
    ?>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 small fw-bold mb-3">Status Saat Ini</span>
                        <h5 class="fw-bold mb-1">
                            <i class="bi <?= $provData['icon'] ?? 'bi-robot' ?> me-2"></i>
                            <?= htmlspecialchars($provData['name'] ?? ucfirst($currProv)) ?>
                        </h5>
                        <p class="small text-white-50 mb-3">Model aktif yang digunakan oleh AI Assistant kampus.</p>
                    </div>
                    <div class="p-3 bg-white bg-opacity-10 rounded-3">
                        <small class="d-block text-white-50">Model AI Terpilih:</small>
                        <span class="font-monospace fw-bold fs-6 text-white text-break"><?= htmlspecialchars($currModel) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 small fw-bold mb-3">Kredensial API</span>
                        <h6 class="fw-bold mb-2">API Key Aktif</h6>
                        <p class="small text-muted mb-3">Disimpan aman dalam sistem terenkripsi environment.</p>
                    </div>
                    <div class="p-3 bg-light rounded-3 border">
                        <small class="d-block text-muted">Masked Key:</small>
                        <span class="font-monospace text-dark fw-semibold text-break"><?= htmlspecialchars($maskedKey) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 small fw-bold mb-3">Keamanan & Fallback</span>
                        <h6 class="fw-bold mb-2">Zero-Hallucination Safe</h6>
                        <p class="small text-muted mb-0">
                            Didukung <strong>Rule-Based Engine lokal</strong> otomatis. Jika kuota API habis atau server offline, AI Assistant tetap menjawab data fasilitas kampus tanpa error.
                        </p>
                    </div>
                    <div class="mt-3">
                        <span class="badge bg-success px-3 py-2 rounded-pill small">
                            <i class="bi bi-shield-check me-1"></i> Local Fallback Siap
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Configuration Form -->
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-header bg-white border-bottom p-4">
            <h5 class="fw-bold mb-1">
                <i class="bi bi-sliders2-vertical text-primary me-2"></i> Formulir Perubahan Model & Kredensial AI
            </h5>
            <p class="text-muted small mb-0">Pilih provider yang diinginkan, tentukan model, masukkan API Key, dan uji koneksi secara langsung.</p>
        </div>
        <div class="card-body p-4">
            <form action="<?= base_url('ai/save-pengaturan') ?>" method="POST" id="formAiConfig">
                <?= csrf_field() ?>

                <!-- Step 1: Pilih Provider AI -->
                <div class="mb-4">
                    <label class="form-label fw-bold text-dark mb-2">
                        1. Pilih Provider / Mesin AI <span class="text-danger">*</span>
                    </label>
                    <div class="row g-3" id="providerCardsContainer">
                        <?php foreach ($providers as $key => $p): ?>
                            <?php $isSelected = ($currProv === $key); ?>
                            <div class="col-md-6 col-lg-4">
                                <label class="provider-card-wrapper w-100 cursor-pointer h-100">
                                    <input type="radio" name="provider" value="<?= $key ?>" class="d-none provider-radio" <?= $isSelected ? 'checked' : '' ?>>
                                    <div class="card h-100 rounded-4 border-2 p-3 provider-card transition-all <?= $isSelected ? 'border-primary bg-primary-subtle shadow-sm' : 'border-light-subtle bg-white' ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="provider-icon-box rounded-3 p-2 bg-<?= $p['badge_color'] ?> text-white d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi <?= $p['icon'] ?> fs-5"></i>
                                            </div>
                                            <span class="badge bg-<?= $p['badge_color'] ?> rounded-pill small"><?= $p['badge'] ?></span>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($p['name']) ?></h6>
                                        <small class="text-muted d-block mb-3">Model default: <code><?= htmlspecialchars($p['default_model']) ?></code></small>
                                        <div class="mt-auto d-flex justify-content-between align-items-center">
                                            <span class="text-primary small fw-semibold select-label">
                                                <?= $isSelected ? '<i class="bi bi-check-circle-fill"></i> Terpilih' : 'Pilih Provider' ?>
                                            </span>
                                            <?php if ($p['docs_url'] !== '#'): ?>
                                                <a href="<?= $p['docs_url'] ?>" target="_blank" class="small text-decoration-none text-muted" onclick="event.stopPropagation();">
                                                    Dapatkan API Key <i class="bi bi-box-arrow-up-right"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Step 2: Pilih Model AI -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label for="selectModel" class="form-label fw-bold text-dark mb-1">
                            2. Pilih Model AI <span class="text-danger">*</span>
                        </label>
                        <p class="text-muted small mb-2">Daftar model yang dioptimalkan untuk sistem fasilitas kampus.</p>
                        <select class="form-select form-select-lg rounded-3 fs-6" id="selectModel" name="model" required>
                            <!-- Diisi via JavaScript sesuai provider yang dipilih -->
                        </select>
                        <div class="form-text mt-1" id="modelHelpText">
                            Pilih model yang tersedia atau pilih opsi 'Model Kustom' untuk memasukkan nama model spesifik.
                        </div>
                    </div>

                    <!-- Custom Model Input (Ditampilkan jika dipilih opsi 'custom') -->
                    <div class="col-md-6" id="customModelWrapper" style="display: none;">
                        <label for="customModel" class="form-label fw-bold text-dark mb-1">
                            Model Kustom (Spesifik)
                        </label>
                        <p class="text-muted small mb-2">Contoh: <code>anthropic/claude-3.5-sonnet</code> atau <code>deepseek-chat</code></p>
                        <input type="text" class="form-control form-control-lg rounded-3 fs-6 font-monospace" id="customModel" name="custom_model" placeholder="Masukkan ID model lengkap...">
                    </div>
                </div>

                <!-- Step 3: Masukkan API Key -->
                <div class="mb-4" id="apiKeySection">
                    <label for="inputApiKey" class="form-label fw-bold text-dark mb-1">
                        3. API Key Provider <span class="text-danger" id="apiKeyRequiredMark">*</span>
                    </label>
                    <p class="text-muted small mb-2" id="apiKeyHelpText">
                        Kunci otentikasi API yang diterbitkan oleh penyedia layanan AI.
                    </p>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light text-muted border-end-0 rounded-start-3">
                            <i class="bi bi-key-fill"></i>
                        </span>
                        <input type="password" class="form-control font-monospace fs-6 border-start-0 border-end-0" id="inputApiKey" name="api_key" value="<?= htmlspecialchars($currKey) ?>" placeholder="Masukkan API Key...">
                        <button class="btn btn-outline-secondary border-start-0 rounded-end-3 px-3" type="button" id="btnToggleApiKey" title="Lihat / Sembunyikan API Key">
                            <i class="bi bi-eye" id="iconToggleKey"></i>
                        </button>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                        <small class="text-muted" id="apiKeyPortalLink">
                            Belum punya API Key?
                            <a href="https://aistudio.google.com/app/apikey" target="_blank" class="fw-semibold text-primary" id="linkGetApiKey">
                                Ambil gratis di Google AI Studio &rarr;
                            </a>
                        </small>
                        <small class="text-secondary">
                            <i class="bi bi-shield-check text-success me-1"></i> API Key tidak pernah dibagikan ke publik.
                        </small>
                    </div>
                </div>

                <!-- Live Test Connection Box -->
                <div class="card border border-light-subtle rounded-4 bg-light p-3 mb-4" id="testBox">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="bi bi-broadcast text-info me-1"></i> Uji Coba Koneksi AI Langsung
                            </h6>
                            <small class="text-muted">Pastikan API Key dan Model yang dipilih aktif dan dapat merespons sebelum menyimpan.</small>
                        </div>
                        <button type="button" class="btn btn-dark rounded-pill px-4 fw-semibold shadow-sm" id="btnTestConnection">
                            <span class="spinner-border spinner-border-sm me-1 d-none" id="testSpinner" role="status" aria-hidden="true"></span>
                            <i class="bi bi-play-circle-fill me-1" id="testPlayIcon"></i> Uji Koneksi Sekarang
                        </button>
                    </div>

                    <!-- Test Output Alert (AJAX result) -->
                    <div class="mt-3 d-none" id="testResultBox"></div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-end align-items-center gap-2 pt-2 border-top">
                    <a href="<?= base_url('dashboard') ?>" class="btn btn-light rounded-pill px-4 fw-semibold text-muted">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow">
                        <i class="bi bi-check-lg me-1"></i> Simpan & Terapkan Konfigurasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.cursor-pointer {
    cursor: pointer;
}
.transition-all {
    transition: all 0.2s ease-in-out;
}
.provider-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.08) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Definisi data provider & model dari backend PHP
    const providersData = <?= json_encode($providers, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
    const currentConfig = <?= json_encode($current_config, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    const providerRadios = document.querySelectorAll('.provider-radio');
    const selectModel = document.getElementById('selectModel');
    const customModelWrapper = document.getElementById('customModelWrapper');
    const customModelInput = document.getElementById('customModel');
    const inputApiKey = document.getElementById('inputApiKey');
    const btnToggleApiKey = document.getElementById('btnToggleApiKey');
    const iconToggleKey = document.getElementById('iconToggleKey');
    const linkGetApiKey = document.getElementById('linkGetApiKey');
    const apiKeySection = document.getElementById('apiKeySection');
    const apiKeyRequiredMark = document.getElementById('apiKeyRequiredMark');
    const btnTestConnection = document.getElementById('btnTestConnection');
    const testSpinner = document.getElementById('testSpinner');
    const testPlayIcon = document.getElementById('testPlayIcon');
    const testResultBox = document.getElementById('testResultBox');

    // Toggle Mask Password API Key
    btnToggleApiKey.addEventListener('click', function () {
        if (inputApiKey.type === 'password') {
            inputApiKey.type = 'text';
            iconToggleKey.classList.replace('bi-eye', 'bi-eye-slash');
        } else {
            inputApiKey.type = 'password';
            iconToggleKey.classList.replace('bi-eye-slash', 'bi-eye');
        }
    });

    // Fungsi Render Dropdown Model berdasarkan Provider
    function populateModelsForProvider(providerKey, selectedModelValue) {
        const prov = providersData[providerKey];
        if (!prov) return;

        selectModel.innerHTML = '';

        let matched = false;
        if (prov.models) {
            for (const [modelKey, modelLabel] of Object.entries(prov.models)) {
                const opt = document.createElement('option');
                opt.value = modelKey;
                opt.textContent = modelLabel;
                if (modelKey === selectedModelValue) {
                    opt.selected = true;
                    matched = true;
                }
                selectModel.appendChild(opt);
            }
        }

        // Tambah opsi custom jika bukan rule_engine
        if (providerKey !== 'rule_engine') {
            const customOpt = document.createElement('option');
            customOpt.value = 'custom';
            customOpt.textContent = '⚙️ Model Kustom / Manual...';
            if (!matched && selectedModelValue && selectedModelValue !== prov.default_model) {
                customOpt.selected = true;
                customModelInput.value = selectedModelValue;
                customModelWrapper.style.display = 'block';
            }
            selectModel.appendChild(customOpt);
        }

        // Tampilkan/sembunyikan input custom
        if (selectModel.value === 'custom') {
            customModelWrapper.style.display = 'block';
        } else {
            customModelWrapper.style.display = 'none';
        }

        // Atur placeholder & tautan API Key
        if (providerKey === 'rule_engine') {
            inputApiKey.disabled = true;
            inputApiKey.placeholder = 'Tidak memerlukan API Key (Offline)';
            apiKeyRequiredMark.style.display = 'none';
            if (linkGetApiKey) linkGetApiKey.style.display = 'none';
        } else {
            inputApiKey.disabled = false;
            inputApiKey.placeholder = prov.key_placeholder || 'Masukkan API Key...';
            apiKeyRequiredMark.style.display = 'inline';
            if (linkGetApiKey && prov.docs_url !== '#') {
                linkGetApiKey.style.display = 'inline';
                linkGetApiKey.href = prov.docs_url;
                linkGetApiKey.innerHTML = `Dapatkan API Key di portal ${prov.name} &rarr;`;
            } else if (linkGetApiKey) {
                linkGetApiKey.style.display = 'none';
            }
        }
    }

    // Handler Perubahan Dropdown Model
    selectModel.addEventListener('change', function () {
        if (this.value === 'custom') {
            customModelWrapper.style.display = 'block';
            customModelInput.focus();
        } else {
            customModelWrapper.style.display = 'none';
        }
    });

    // Handler Klik Kartu Provider
    providerRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            // Perbarui visual border seluruh kartu
            document.querySelectorAll('.provider-card').forEach(card => {
                card.classList.remove('border-primary', 'bg-primary-subtle', 'shadow-sm');
                card.classList.add('border-light-subtle', 'bg-white');
                const label = card.querySelector('.select-label');
                if (label) label.innerHTML = 'Pilih Provider';
            });

            const currentCard = this.closest('.provider-card-wrapper').querySelector('.provider-card');
            if (currentCard) {
                currentCard.classList.remove('border-light-subtle', 'bg-white');
                currentCard.classList.add('border-primary', 'bg-primary-subtle', 'shadow-sm');
                const label = currentCard.querySelector('.select-label');
                if (label) label.innerHTML = '<i class="bi bi-check-circle-fill"></i> Terpilih';
            }

            const chosenProvider = this.value;
            const defaultModel = providersData[chosenProvider]?.default_model || '';
            populateModelsForProvider(chosenProvider, defaultModel);
        });
    });

    // Inisialisasi awal form dengan konfigurasi aktif saat ini
    const initialProvider = currentConfig.provider || 'gemini';
    const initialModel = currentConfig.model || 'gemini-1.5-flash';
    populateModelsForProvider(initialProvider, initialModel);

    // Live Test Connection Handler via AJAX
    btnTestConnection.addEventListener('click', async function () {
        const selectedRadio = document.querySelector('.provider-radio:checked');
        const provider = selectedRadio ? selectedRadio.value : 'gemini';
        let model = selectModel.value;
        if (model === 'custom') {
            model = customModelInput.value.trim();
        }
        const apiKey = inputApiKey.value.trim();

        // Validasi client-side cepat
        if (provider !== 'rule_engine' && !apiKey) {
            testResultBox.className = 'mt-3 alert alert-warning border-0 rounded-3 small';
            testResultBox.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Harap masukkan API Key terlebih dahulu sebelum melakukan uji coba.';
            testResultBox.classList.remove('d-none');
            inputApiKey.focus();
            return;
        }

        // Tampilkan indikator loading
        testSpinner.classList.remove('d-none');
        testPlayIcon.classList.add('d-none');
        btnTestConnection.disabled = true;
        testResultBox.classList.add('d-none');

        try {
            const response = await fetch('<?= base_url('ai/test-connection') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    provider: provider,
                    model: model,
                    api_key: apiKey
                })
            });

            const result = await response.json();

            testResultBox.classList.remove('d-none');
            if (result.status === 'success') {
                if (result.active_model && result.active_model !== model) {
                    let foundOpt = Array.from(selectModel.options).find(o => o.value === result.active_model);
                    if (foundOpt) {
                        selectModel.value = result.active_model;
                    } else {
                        selectModel.value = 'custom';
                        customModelWrapper.style.display = 'block';
                        customModelInput.value = result.active_model;
                    }
                }

                testResultBox.className = 'mt-3 alert alert-success border-0 rounded-3 small';
                testResultBox.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <strong class="d-block"><i class="bi bi-check-circle-fill text-success me-1"></i> Koneksi Berhasil!</strong>
                            <span>${result.message}</span>
                        </div>
                        <span class="badge bg-success font-monospace px-3 py-1">Latensi: ${result.latency_ms || 0} ms</span>
                    </div>
                `;
            } else {
                testResultBox.className = 'mt-3 alert alert-danger border-0 rounded-3 small';
                testResultBox.innerHTML = `
                    <strong class="d-block"><i class="bi bi-x-circle-fill text-danger me-1"></i> Koneksi Gagal</strong>
                    <span>${result.message || 'Terjadi kesalahan saat menghubungi API provider.'}</span>
                `;
            }
        } catch (err) {
            testResultBox.classList.remove('d-none');
            testResultBox.className = 'mt-3 alert alert-danger border-0 rounded-3 small';
            testResultBox.innerHTML = `
                <strong class="d-block"><i class="bi bi-x-circle-fill text-danger me-1"></i> Error Jaringan</strong>
                <span>Tidak dapat terhubung ke server internal untuk uji coba (${err.message}).</span>
            `;
        } finally {
            testSpinner.classList.add('d-none');
            testPlayIcon.classList.remove('d-none');
            btnTestConnection.disabled = false;
        }
    });
});
</script>

