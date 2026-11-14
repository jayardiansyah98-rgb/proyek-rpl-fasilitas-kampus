/**
 * Test Frontend DOM & Integration
 * Memastikan floating button dan popup chatbot beranda STMIK IKMI Cirebon
 * memenuhi seluruh requirement spesifikasi user.
 */

const http = require('http');

async function testFrontend() {
    console.log('=== MEMULAI TEST FRONTEND CHATBOT FLOATING & POPUP ===\n');

    return new Promise((resolve, reject) => {
        http.get('http://localhost/Fasilitas%20Kampus/public/', (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                let passed = 0;
                let failed = 0;

                function assert(condition, message) {
                    if (condition) {
                        console.log(`[PASS] ${message}`);
                        passed++;
                    } else {
                        console.error(`[FAIL] ${message}`);
                        failed++;
                    }
                }

                assert(res.statusCode === 200, '1. Homepage HTTP Status 200 OK');

                // 2. Tombol Floating
                const toggleBtnMatches = (data.match(/id=["']chatbot-toggle-btn["']/g) || []).length;
                assert(toggleBtnMatches === 1, `2a. Terdapat tepat 1 tombol floating chatbot (ditemukan: ${toggleBtnMatches})`);

                const hasAriaLabel = data.includes('aria-label="Buka Asisten Fasilitas IKMI"');
                assert(hasAriaLabel, '2b. Tombol floating memiliki aria-label="Buka Asisten Fasilitas IKMI"');

                const hasRobotIconInToggle = data.includes('bi-robot') && data.includes('chatbot-float-btn');
                assert(hasRobotIconInToggle, '2c. Tombol floating menggunakan ikon robot modern');

                const hasOnlineBadge = data.includes('chatbot-float-badge');
                assert(hasOnlineBadge, '2d. Tombol floating memiliki status badge online');

                // 3. Popup Panel Chatbot
                const panelMatches = (data.match(/id=["']chatbot-panel["']/g) || []).length;
                assert(panelMatches === 1, `3a. Terdapat tepat 1 panel popup chatbot (ditemukan: ${panelMatches})`);

                // Pastikan panel memiliki d-none secara default
                const panelDefaultHidden = /id=["']chatbot-panel["'][^>]*class=["'][^"']*d-none[^"']*["']/.test(data);
                assert(panelDefaultHidden, '3b. Panel popup tertutup secara default saat dimuat (memiliki class d-none)');

                // Header dan judul
                assert(data.includes('Asisten Fasilitas IKMI'), '3c. Header popup menampilkan judul "Asisten Fasilitas IKMI"');
                assert(data.includes('id="chatbot-close-btn"'), '3d. Header memiliki tombol close X (#chatbot-close-btn)');

                // Area input dan 5 pertanyaan cepat
                assert(data.includes('id="chatbot-input"'), '4a. Area input tersedia (#chatbot-input)');
                assert(data.includes('Ketik pertanyaan fasilitas kampus...'), '4b. Placeholder input sesuai spesifikasi');
                assert(data.includes('id="chatbot-send-btn"'), '4c. Tombol kirim tersedia (#chatbot-send-btn)');

                const quickPromptMatches = (data.match(/quick-prompt-btn/g) || []).length;
                assert(quickPromptMatches === 5, `5. Terdapat tepat 5 tombol pertanyaan cepat (ditemukan: ${quickPromptMatches})`);

                // Asset files
                assert(data.includes('style.css'), '6a. Memuat file stylesheet utama (style.css)');
                assert(data.includes('chatbot.js'), '6b. Memuat file skrip chatbot (chatbot.js)');

                console.log(`\n============================================`);
                console.log(`HASIL TEST FRONTEND: ${passed} PASS / ${failed} FAIL`);
                if (failed === 0) {
                    console.log(`SELURUH PERSYARATAN FRONTEND TERPENUHI DENGAN SEMPURNA! (100% OK)`);
                    resolve(true);
                } else {
                    reject(new Error(`${failed} test gagal!`));
                }
            });
        }).on('error', (err) => {
            console.error('Koneksi ke Apache gagal:', err.message);
            reject(err);
        });
    });
}

testFrontend().catch(() => process.exit(1));
