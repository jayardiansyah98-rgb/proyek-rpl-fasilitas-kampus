/**
 * Backend Chatbot Service (Peningkatan Konteks Database & Identitas Pengguna)
 * Sistem Informasi Pendataan Fasilitas Ruangan Kampus STMIK IKMI Cirebon
 * Teknologi: Node.js, Express, dotenv, cors, mysql2
 * Port: 3001
 *
 * v2.1 — Contextual Follow-Up & Multi-Turn Conversation History
 */

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');
const os = require('os');

const app = express();
const PORT = process.env.PORT || 3001;
const ALLOWED_ORIGIN = process.env.ALLOWED_ORIGIN || '*';
let AI_API_KEY = process.env.AI_API_KEY || '';
let AI_MODEL = process.env.AI_MODEL || 'gemini-1.5-flash';
let AI_PROVIDER = process.env.AI_PROVIDER || 'gemini';

// Database Pool (MySQL)
const dbPool = mysql.createPool({
    host: process.env.DB_HOST || '127.0.0.1',
    port: parseInt(process.env.DB_PORT || '3306', 10),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASS || '',
    database: process.env.DB_NAME || 'db_fasilitas_ikmi',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Test Koneksi Database
dbPool.getConnection()
    .then(conn => {
        console.log(`[DB] Berhasil terhubung ke database MySQL '${process.env.DB_NAME || 'db_fasilitas_ikmi'}' di port ${process.env.DB_PORT || '3306'}.`);
        conn.release();
    })
    .catch(err => {
        console.warn(`[DB WARN] Koneksi database belum aktif atau gagal: ${err.message}. Chatbot tetap dapat berjalan dengan penanganan error yang aman.`);
    });

// Middleware CORS dengan dukungan Kredensial & Custom Header Sesi
const corsOptions = {
    origin: (origin, callback) => {
        if (!origin || ALLOWED_ORIGIN === '*' || origin.includes('localhost') || origin.includes('127.0.0.1')) {
            callback(null, true);
        } else if (origin === ALLOWED_ORIGIN) {
            callback(null, true);
        } else {
            callback(new Error('Origin tidak diizinkan oleh CORS'));
        }
    },
    credentials: true,
    methods: ['GET', 'POST', 'OPTIONS'],
    allowedHeaders: ['Content-Type', 'Authorization', 'X-Session-ID']
};

app.use(cors(corsOptions));
app.use(express.json({ limit: '10kb' }));

// Pesan Baku Penolakan Sesuai Ketentuan Kebijakan Project
const CODE_REFUSAL_MESSAGE =
    'Maaf, saya dirancang untuk membantu informasi fasilitas kampus, data ruangan, inventaris, dan laporan kerusakan. Saya tidak dapat membuat atau menampilkan kode program.';

const OUT_OF_CONTEXT_MESSAGE =
    'Maaf, saya hanya dapat membantu pertanyaan terkait Sistem Informasi Pendataan Fasilitas Ruangan Kampus STMIK IKMI Cirebon dan data yang tersedia di sistem.';

const DB_UNAVAILABLE_MESSAGE =
    'Terjadi kendala koneksi ke database kampus. Informasi fasilitas belum dapat diambil saat ini. Silakan coba beberapa saat lagi.';

// =============================================================================
// IN-MEMORY CONVERSATION HISTORY STORE (per chatSessionId)
// Key  : chatSessionId (string, dikirim oleh frontend)
// Value: { history: [{role, text}], lastAccess: timestamp }
// TTL  : 30 menit sejak pesan terakhir (dibersihkan setiap 10 menit)
// =============================================================================
const conversationStore = new Map();
const CONV_TTL_MS = 30 * 60 * 1000; // 30 menit
const MAX_HISTORY_TURNS = 10;        // simpan maks 10 pasang tanya-jawab

// Bersihkan sesi yang sudah kadaluarsa setiap 10 menit
setInterval(() => {
    const now = Date.now();
    for (const [key, val] of conversationStore.entries()) {
        if (now - val.lastAccess > CONV_TTL_MS) {
            conversationStore.delete(key);
        }
    }
}, 10 * 60 * 1000);

/**
 * Ambil atau buat sesi percakapan berdasarkan chatSessionId.
 */
function getOrCreateConvSession(chatSessionId) {
    if (!chatSessionId || typeof chatSessionId !== 'string') return null;
    const key = chatSessionId.trim().substring(0, 128);
    if (!key) return null;

    if (!conversationStore.has(key)) {
        conversationStore.set(key, { history: [], lastAccess: Date.now() });
    }
    const session = conversationStore.get(key);
    session.lastAccess = Date.now();
    return session;
}

/**
 * Simpan pasang tanya-jawab ke riwayat sesi.
 */
function pushToHistory(session, userText, botText) {
    if (!session) return;
    session.history.push({ role: 'user', text: userText });
    session.history.push({ role: 'model', text: botText });
    // Potong dari depan agar tidak melebihi MAX_HISTORY_TURNS pasang
    const maxEntries = MAX_HISTORY_TURNS * 2;
    if (session.history.length > maxEntries) {
        session.history = session.history.slice(session.history.length - maxEntries);
    }
}

/**
 * Detektor Ketat Permintaan Kode Program (Pre-Check Berlapis)
 */
function isCodingRequest(text) {
    if (!text || typeof text !== 'string') return false;
    const lower = text.toLowerCase().trim();

    const directPhrases = [
        'coding', 'koding', 'source code', 'sourcecode', 'source-code',
        'program sederhana', 'contoh program', 'buatkan program', 'tuliskan program',
        'bikin program', 'buat program', 'tulis program', 'membuat program',
        'contoh kode', 'buatkan kode', 'tuliskan kode', 'bikin kode', 'buat kode', 'tulis kode',
        'contoh script', 'buatkan script', 'tuliskan script', 'bikin script', 'buat script',
        'contoh skrip', 'buatkan skrip', 'tuliskan skrip', 'bikin skrip', 'buat skrip',
        'code snippet', 'snippet kode', 'debugging', 'debug kode', 'debug program',
        'perbaiki kode', 'jalankan kode', 'eksekusi kode', 'compile kode',
        'belajar koding', 'belajar coding', 'syntax', 'sintaks kode', 'sintaks program'
    ];

    if (directPhrases.some(phrase => lower.includes(phrase))) {
        return true;
    }

    const programmingLangs = [
        'python', 'javascript', 'typescript', 'php', 'java', 'c\\+\\+', 'cpp',
        'c#', 'csharp', 'golang', 'ruby', 'rust', 'sql', 'html', 'css',
        'bash', 'shell', 'powershell', 'perl', 'swift', 'kotlin', 'dart', 'assembly'
    ];

    const codingVerbs = [
        'buatkan', 'buat', 'tuliskan', 'tulis', 'bikin', 'tampilkan', 'berikan',
        'minta', 'contoh', 'generate', 'write', 'create', 'print', 'code', 'kode',
        'program', 'script', 'skrip', 'fungsi', 'function', 'class', 'modul'
    ];

    const langRegex = new RegExp(`\\b(${programmingLangs.join('|')})\\b`, 'i');
    const verbRegex = new RegExp(`\\b(${codingVerbs.join('|')})\\b`, 'i');

    if (langRegex.test(lower) && verbRegex.test(lower)) {
        return true;
    }

    const syntaxPatterns = [
        /def\s+[a-zA-Z0-9_]+\s*\(/i,
        /function\s+[a-zA-Z0-9_]*\s*\(/i,
        /console\.(log|warn|error)\s*\(/i,
        /print\s*\(.*\)/i,
        /echo\s+['"$]/i,
        /System\.out\.print/i,
        /<\?php/i,
        /<script\b[^>]*>/i,
        /import\s+(sys|os|re|math|numpy|pandas)|from\s+[a-zA-Z0-9_]+\s+import/i,
        /#include\s*<[a-zA-Z0-9_.]+>/i,
        /public\s+class\s+[a-zA-Z0-9_]+/i,
        /SELECT\s+.+\s+FROM\s+/i
    ];

    return syntaxPatterns.some(pattern => pattern.test(text));
}

/**
 * Detektor Pertanyaan di Luar Konteks Sistem Informasi Fasilitas Kampus
 */
function isOutOfContextRequest(text) {
    if (!text || typeof text !== 'string') return false;
    const lower = text.toLowerCase().trim();

    // 1. Politik, Presiden, Pejabat Negara Lain / Umum
    const politicalPatterns = [
        'presiden', 'perdana menteri', 'menteri', 'gubernur', 'bupati', 'walikota',
        'dpr', 'partai politik', 'pemilu', 'pilpres', 'negara lain',
        'prabowo', 'jokowi', 'biden', 'trump', 'obama', 'putin', 'presiden amerika',
        'presiden rusia', 'presiden prancis', 'presiden jepang', 'raja inggris'
    ];
    if (politicalPatterns.some(p => lower.includes(p))) {
        return true;
    }

    // 2. Hiburan, Masak / Resep, Gosip, Zodiak, Game, Cuaca, Olahraga Luar
    const offTopicTopics = [
        'resep', 'cara memasak', 'bumbu dapur', 'masak nasi', 'goreng',
        'film', 'sinopsis film', 'lirik lagu', 'chord gitar', 'chord lagu',
        'zodiak', 'horoskop', 'ramalan', 'tanda bintang',
        'cuaca hari ini', 'prakiraan cuaca',
        'sepak bola', 'liga inggris', 'liga champion', 'piala dunia',
        'game online', 'mobile legends', 'free fire', 'pubg', 'genshin'
    ];
    if (offTopicTopics.some(t => lower.includes(t))) {
        return true;
    }

    // 3. Pengetahuan Umum Non-Kampus (Astronomi, Sains Murni, Geografi Luar)
    const generalTrivia = [
        'jarak bumi ke bulan', 'jarak matahari ke bumi', 'tata surya',
        'kecepatan cahaya', 'dinosaurus', 'gunung tertinggi di dunia',
        'sungai terpanjang di dunia', 'ibukota prancis', 'ibukota jepang'
    ];
    if (generalTrivia.some(g => lower.includes(g))) {
        return true;
    }

    // 4. Prompt Injection & Bypass Percobaan Pengubahan Instruksi
    const promptInjection = [
        'abaikan instruksi', 'abaikan perintah', 'ignore previous instructions',
        'ignore all instructions', 'lupakan instruksi sebelumnya', 'lupakan perintah',
        'jailbreak', 'prompt injection', 'system prompt', 'developer mode',
        'mode pengembang', 'pretend you are', 'act as a', 'kamu sekarang adalah'
    ];
    if (promptInjection.some(pi => lower.includes(pi))) {
        return true;
    }

    // 5. Permintaan Kredensial, Password, Token, Konfigurasi Rahasia
    const credentialKeywords = [
        'tampilkan password', 'password admin', 'hash password', 'kata sandi',
        'bocorkan password', 'isi env', 'environment variable', 'kredensial database',
        'api_key', 'secret key', 'jwt token', 'config rahasia'
    ];
    if (credentialKeywords.some(c => lower.includes(c))) {
        return true;
    }

    return false;
}

/**
 * Sanitasi Respons Chatbot (Post-Check Berlapis)
 */
function sanitizeResponse(text) {
    if (!text || typeof text !== 'string') return text;

    if (/```[\s\S]*?```/.test(text)) {
        return CODE_REFUSAL_MESSAGE;
    }

    if (/`[^`]*(print\(|console\.log|def |function |SELECT |<\?php)[^`]*`/i.test(text)) {
        return CODE_REFUSAL_MESSAGE;
    }

    const syntaxLeakPatterns = [
        /(def\s+[a-zA-Z0-9_]+\s*\(|function\s+[a-zA-Z0-9_]*\s*\(|console\.log\s*\(|print\s*\(|<\?php|<script\b)/i,
        /(public\s+class\s+|#include\s*<|import\s+[a-zA-Z0-9_.]+|from\s+[a-zA-Z0-9_.]+\s+import)/i,
        /SELECT\s+.+\s+FROM\s+/i
    ];

    for (const pattern of syntaxLeakPatterns) {
        if (pattern.test(text)) {
            return CODE_REFUSAL_MESSAGE;
        }
    }

    return text;
}

/**
 * Integrasi & Resolusi Sesi Server-Side PHP (Anti-Pemalsuan Identitas)
 * Tidak mempercayai nama atau role dari payload frontend.
 * Menjamin identitas hanya berasal dari session file PHP valid / verifikasi backend.
 */
async function resolveUserSession(req) {
    try {
        // Ekstrak Session ID dari Header X-Session-ID, Body, atau Cookie PHPSESSID
        let sid = req.headers['x-session-id'] || req.body?.sessionId;
        if (!sid && req.headers.cookie) {
            const match = req.headers.cookie.match(/PHPSESSID=([a-zA-Z0-9,-]+)/);
            if (match) sid = match[1];
        }

        if (!sid || typeof sid !== 'string') return null;
        sid = sid.trim();
        if (!/^[a-zA-Z0-9,-]{16,128}$/.test(sid)) return null;

        let userId = null;
        let lastActivity = 0;

        // Path penyimpanan session file PHP (Laragon tmp / OS temp)
        const sessionDirs = [
            process.env.PHP_SESSION_PATH,
            'C:/laragon/tmp',
            os.tmpdir()
        ].filter(Boolean);

        for (const dir of sessionDirs) {
            const sessFile = path.join(dir, `sess_${sid}`);
            if (fs.existsSync(sessFile)) {
                try {
                    const content = fs.readFileSync(sessFile, 'utf8');
                    // Ekstrak user id dari format serialisasi PHP session
                    const idMatch = content.match(/s:2:"id";(?:i:(\d+)|s:\d+:"(\d+)");/);
                    const actMatch = content.match(/s:13:"last_activity";i:(\d+);/);
                    if (idMatch) {
                        userId = parseInt(idMatch[1] || idMatch[2], 10);
                    }
                    if (actMatch) {
                        lastActivity = parseInt(actMatch[1], 10);
                    }
                    if (userId) break;
                } catch (e) {
                    console.warn('[Session Read Warning]', e.message);
                }
            }
        }

        // Fallback: Verifikasi dengan internal endpoint PHP jika file belum tersinkronisasi
        if (!userId) {
            try {
                const controller = new AbortController();
                const timeout = setTimeout(() => controller.abort(), 2000);
                const phpUrl = `http://127.0.0.1/Fasilitas%20Kampus/public/auth/session_status?sid=${encodeURIComponent(sid)}`;
                const phpRes = await fetch(phpUrl, { signal: controller.signal });
                clearTimeout(timeout);
                if (phpRes.ok) {
                    const phpData = await phpRes.json();
                    if (phpData.authenticated && phpData.user?.id) {
                        userId = parseInt(phpData.user.id, 10);
                    }
                }
            } catch (e) {
                // Ignore fallback network error
            }
        }

        if (!userId) return null;

        // Validasi Inactivity Timeout Sesi (7200 detik / 2 Jam)
        if (lastActivity > 0) {
            const now = Math.floor(Date.now() / 1000);
            if (now - lastActivity > 7200) {
                return null;
            }
        }

        // Ambil data aktual pengguna dari tabel users & roles via Parameterized Query
        const [rows] = await dbPool.query(`
            SELECT u.id, u.nomor_induk, u.nama_lengkap, u.email, u.jenis_pengguna, u.status,
                   r.nama_role, r.deskripsi AS deskripsi_role
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.id = ? AND u.status = 'aktif'
            LIMIT 1
        `, [userId]);

        if (!rows || rows.length === 0) return null;
        const u = rows[0];

        // Normalisasi role pengguna sistem
        let normalizedRole = 'pengguna';
        let roleDisplay = 'civitas akademika';

        if (u.nama_role === 'admin') {
            normalizedRole = 'admin';
            roleDisplay = 'administrator';
        } else if (u.nama_role === 'petugas_sarpras') {
            normalizedRole = 'petugas_sarpras';
            roleDisplay = 'petugas sarana dan prasarana';
        } else if (u.nama_role === 'pengguna') {
            if (u.jenis_pengguna === 'dosen') {
                normalizedRole = 'dosen';
                roleDisplay = 'dosen';
            } else if (u.jenis_pengguna === 'mahasiswa') {
                normalizedRole = 'mahasiswa';
                roleDisplay = 'mahasiswa';
            } else {
                normalizedRole = 'pengguna';
                roleDisplay = 'civitas akademika';
            }
        }

        return {
            id: u.id,
            nomor_induk: u.nomor_induk,
            nama_lengkap: u.nama_lengkap,
            email: u.email,
            nama_role: u.nama_role,
            jenis_pengguna: u.jenis_pengguna,
            normalized_role: normalizedRole,
            role_display: roleDisplay
        };
    } catch (err) {
        console.error('[Session Resolve Error]', err.message);
        return null;
    }
}

// Helper: Ambil Data Statistik Ringkasan Aktual dari Database
async function getActualStats() {
    try {
        const [gedungRows] = await dbPool.query("SELECT COUNT(*) AS total FROM gedung WHERE status = 'aktif'");
        const [ruanganRows] = await dbPool.query(`
            SELECT 
                COUNT(*) AS total, 
                SUM(CASE WHEN status = 'tersedia' THEN 1 ELSE 0 END) AS tersedia,
                SUM(CASE WHEN status = 'digunakan' THEN 1 ELSE 0 END) AS digunakan,
                SUM(CASE WHEN status = 'perbaikan' THEN 1 ELSE 0 END) AS perbaikan
            FROM ruangan
        `);
        const [fasilitasRows] = await dbPool.query(`
            SELECT 
                COUNT(*) AS total_item, 
                COALESCE(SUM(jumlah), 0) AS total_unit,
                SUM(CASE WHEN kondisi = 'baik' THEN 1 ELSE 0 END) AS kondisi_baik,
                SUM(CASE WHEN kondisi = 'rusak_ringan' THEN 1 ELSE 0 END) AS rusak_ringan,
                SUM(CASE WHEN kondisi = 'rusak_berat' THEN 1 ELSE 0 END) AS rusak_berat,
                SUM(CASE WHEN kondisi = 'tidak_layak' THEN 1 ELSE 0 END) AS tidak_layak
            FROM fasilitas
        `);
        const [laporanRows] = await dbPool.query(`
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('menunggu', 'diproses', 'diverifikasi') THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS selesai
            FROM laporan_kerusakan
        `);
        const [userRows] = await dbPool.query(`
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN r.nama_role = 'admin' THEN 1 ELSE 0 END) AS admin,
                SUM(CASE WHEN r.nama_role = 'petugas_sarpras' THEN 1 ELSE 0 END) AS petugas,
                SUM(CASE WHEN r.nama_role = 'pengguna' AND u.jenis_pengguna = 'dosen' THEN 1 ELSE 0 END) AS dosen,
                SUM(CASE WHEN r.nama_role = 'pengguna' AND u.jenis_pengguna = 'mahasiswa' THEN 1 ELSE 0 END) AS mahasiswa
            FROM users u
            JOIN roles r ON u.role_id = r.id
        `);

        return {
            connected: true,
            gedung: parseInt(gedungRows[0]?.total || 0, 10),
            ruanganTotal: parseInt(ruanganRows[0]?.total || 0, 10),
            ruanganTersedia: parseInt(ruanganRows[0]?.tersedia || 0, 10),
            ruanganDigunakan: parseInt(ruanganRows[0]?.digunakan || 0, 10),
            ruanganPerbaikan: parseInt(ruanganRows[0]?.perbaikan || 0, 10),
            fasilitasItem: parseInt(fasilitasRows[0]?.total_item || 0, 10),
            fasilitasUnit: parseInt(fasilitasRows[0]?.total_unit || 0, 10),
            fasilitasBaik: parseInt(fasilitasRows[0]?.kondisi_baik || 0, 10),
            fasilitasRusakRingan: parseInt(fasilitasRows[0]?.rusak_ringan || 0, 10),
            fasilitasRusakBerat: parseInt(fasilitasRows[0]?.rusak_berat || 0, 10),
            fasilitasTidakLayak: parseInt(fasilitasRows[0]?.tidak_layak || 0, 10),
            totalLaporan: parseInt(laporanRows[0]?.total || 0, 10),
            laporanPending: parseInt(laporanRows[0]?.pending || 0, 10),
            laporanSelesai: parseInt(laporanRows[0]?.selesai || 0, 10),
            usersTotal: parseInt(userRows[0]?.total || 0, 10),
            usersAdmin: parseInt(userRows[0]?.admin || 0, 10),
            usersPetugas: parseInt(userRows[0]?.petugas || 0, 10),
            usersDosen: parseInt(userRows[0]?.dosen || 0, 10),
            usersMahasiswa: parseInt(userRows[0]?.mahasiswa || 0, 10)
        };
    } catch (error) {
        console.error('[DB Query Error]', error.message);
        return { connected: false, error: error.message };
    }
}

// Helper: Ambil Daftar Ruangan beserta Gedungnya
async function getRoomList(limit = 20) {
    try {
        const [rows] = await dbPool.query(`
            SELECT r.nama_ruangan, r.kode_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung,
                   COUNT(f.id) AS total_item,
                   COALESCE(SUM(f.jumlah), 0) AS total_unit
            FROM ruangan r
            JOIN gedung g ON r.gedung_id = g.id
            LEFT JOIN fasilitas f ON r.id = f.ruangan_id
            GROUP BY r.id, r.nama_ruangan, r.kode_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung
            ORDER BY r.nama_ruangan ASC
            LIMIT ?
        `, [limit]);
        return rows;
    } catch (e) {
        return [];
    }
}

// Helper: Ambil Daftar Ruangan yang Berstatus Tersedia
async function getAvailableRoomList(limit = 20) {
    try {
        const [rows] = await dbPool.query(`
            SELECT r.nama_ruangan, r.kode_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung,
                   COUNT(f.id) AS total_item,
                   COALESCE(SUM(f.jumlah), 0) AS total_unit
            FROM ruangan r
            JOIN gedung g ON r.gedung_id = g.id
            LEFT JOIN fasilitas f ON r.id = f.ruangan_id
            WHERE r.status = 'tersedia'
            GROUP BY r.id, r.nama_ruangan, r.kode_ruangan, r.lantai, r.jenis_ruangan, r.kapasitas, r.status, g.nama_gedung
            ORDER BY r.nama_ruangan ASC
            LIMIT ?
        `, [limit]);
        return rows;
    } catch (e) {
        return [];
    }
}

// Helper: Ambil Daftar Gedung Aktif
async function getBuildingList() {
    try {
        const [rows] = await dbPool.query(`
            SELECT kode_gedung, nama_gedung, lokasi, jumlah_lantai, deskripsi
            FROM gedung
            WHERE status = 'aktif'
            ORDER BY kode_gedung ASC
        `);
        return rows;
    } catch (e) {
        return [];
    }
}

// Helper: Ambil Kategori Fasilitas
async function getCategoryList() {
    try {
        const [rows] = await dbPool.query(`
            SELECT k.nama_kategori, COUNT(f.id) AS total_item, COALESCE(SUM(f.jumlah), 0) AS total_unit
            FROM kategori_fasilitas k
            LEFT JOIN fasilitas f ON k.id = f.kategori_id
            GROUP BY k.id, k.nama_kategori
            ORDER BY total_unit DESC
        `);
        return rows;
    } catch (e) {
        return [];
    }
}

// Helper: Ambil Daftar Semua Fasilitas (nama, jenis/kategori, lokasi)
async function getFacilityList(limit = 50) {
    try {
        const [rows] = await dbPool.query(`
            SELECT f.nama, f.kode_inventaris, f.jumlah, f.kondisi,
                   k.nama_kategori,
                   r.nama_ruangan, g.nama_gedung
            FROM fasilitas f
            JOIN ruangan r ON f.ruangan_id = r.id
            JOIN gedung g ON r.gedung_id = g.id
            LEFT JOIN kategori_fasilitas k ON f.kategori_id = k.id
            ORDER BY k.nama_kategori ASC, f.nama ASC
            LIMIT ?
        `, [limit]);
        return rows;
    } catch (e) {
        return [];
    }
}

// Helper: Ambil Fasilitas Berdasarkan Ruangan Spesifik (by keyword: kode atau nama)
async function getFacilitiesByRoom(keyword) {
    try {
        const like = `%${keyword}%`;
        const [rows] = await dbPool.query(`
            SELECT f.nama, f.kode_inventaris, f.jumlah, f.kondisi, f.keterangan,
                   k.nama_kategori,
                   r.nama_ruangan, r.kode_ruangan, r.lantai, g.nama_gedung
            FROM fasilitas f
            JOIN ruangan r ON f.ruangan_id = r.id
            JOIN gedung g ON r.gedung_id = g.id
            LEFT JOIN kategori_fasilitas k ON f.kategori_id = k.id
            WHERE r.kode_ruangan LIKE ? OR r.nama_ruangan LIKE ?
            ORDER BY k.nama_kategori ASC, f.nama ASC
        `, [like, like]);
        return rows;
    } catch (e) {
        return [];
    }
}

// Helper: Ambil Info Ruangan + Rekapitulasi Fasilitas Berdasarkan Keyword
async function getRoomInfoByKeyword(keyword) {
    try {
        const like = `%${keyword}%`;
        const [rows] = await dbPool.query(`
            SELECT r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan,
                   r.kapasitas, r.status, g.nama_gedung,
                   COUNT(f.id) AS total_item,
                   COALESCE(SUM(f.jumlah), 0) AS total_unit,
                   SUM(CASE WHEN f.kondisi = 'baik' THEN f.jumlah ELSE 0 END) AS unit_baik,
                   SUM(CASE WHEN f.kondisi IN ('rusak_ringan','rusak_berat','tidak_layak','hilang') THEN f.jumlah ELSE 0 END) AS unit_bermasalah
            FROM ruangan r
            JOIN gedung g ON r.gedung_id = g.id
            LEFT JOIN fasilitas f ON r.id = f.ruangan_id
            WHERE r.kode_ruangan LIKE ? OR r.nama_ruangan LIKE ?
            GROUP BY r.id, r.kode_ruangan, r.nama_ruangan, r.lantai, r.jenis_ruangan,
                     r.kapasitas, r.status, g.nama_gedung
            LIMIT 5
        `, [like, like]);
        return rows;
    } catch (e) {
        return [];
    }
}

// Helper: Ambil Fasilitas Rusak
async function getDamagedFacilities(limit = 5) {
    try {
        const [rows] = await dbPool.query(`
            SELECT f.nama, f.kode_inventaris, f.jumlah, f.kondisi, r.nama_ruangan, g.nama_gedung
            FROM fasilitas f
            JOIN ruangan r ON f.ruangan_id = r.id
            JOIN gedung g ON r.gedung_id = g.id
            WHERE f.kondisi IN ('rusak_ringan', 'rusak_berat', 'tidak_layak')
            ORDER BY f.kondisi DESC, f.nama ASC
            LIMIT ?
        `, [limit]);
        return rows;
    } catch (e) {
        return [];
    }
}

// Helper: Ambil Data Laporan Kerusakan Berdasarkan Role (RBAC)
async function getReportsByRole(user) {
    try {
        if (!user) {
            // Pengguna Anonim: hanya statistik umum + saran login
            const [rows] = await dbPool.query(`
                SELECT COUNT(*) AS total,
                       SUM(CASE WHEN status IN ('menunggu', 'diproses', 'diverifikasi') THEN 1 ELSE 0 END) AS pending
                FROM laporan_kerusakan
            `);
            return {
                type: 'anonymous',
                total: parseInt(rows[0]?.total || 0, 10),
                pending: parseInt(rows[0]?.pending || 0, 10)
            };
        }

        if (user.normalized_role === 'admin' || user.normalized_role === 'petugas_sarpras') {
            // Admin & Petugas Sarpras: melihat seluruh tiket laporan kerusakan
            const [rows] = await dbPool.query(`
                SELECT l.kode_laporan, l.deskripsi, l.prioritas, l.status, l.created_at,
                       u.nama_lengkap AS pelapor, f.nama AS fasilitas, r.nama_ruangan, g.nama_gedung
                FROM laporan_kerusakan l
                JOIN users u ON l.user_id = u.id
                JOIN fasilitas f ON l.fasilitas_id = f.id
                JOIN ruangan r ON l.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                ORDER BY l.id DESC
                LIMIT 5
            `);
            const [countRows] = await dbPool.query(`
                SELECT COUNT(*) AS total,
                       SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) AS menunggu,
                       SUM(CASE WHEN status = 'diproses' THEN 1 ELSE 0 END) AS diproses,
                       SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) AS selesai
                FROM laporan_kerusakan
            `);
            return {
                type: 'staff',
                list: rows,
                total: parseInt(countRows[0]?.total || 0, 10),
                menunggu: parseInt(countRows[0]?.menunggu || 0, 10),
                diproses: parseInt(countRows[0]?.diproses || 0, 10),
                selesai: parseInt(countRows[0]?.selesai || 0, 10)
            };
        }

        // Dosen & Mahasiswa: hanya tiket laporan milik mereka sendiri
        const [rows] = await dbPool.query(`
            SELECT l.kode_laporan, l.deskripsi, l.prioritas, l.status, l.created_at,
                   f.nama AS fasilitas, r.nama_ruangan, g.nama_gedung
            FROM laporan_kerusakan l
            JOIN fasilitas f ON l.fasilitas_id = f.id
            JOIN ruangan r ON l.ruangan_id = r.id
            JOIN gedung g ON r.gedung_id = g.id
            WHERE l.user_id = ?
            ORDER BY l.id DESC
            LIMIT 5
        `, [user.id]);

        return {
            type: 'user',
            list: rows
        };
    } catch (e) {
        return { error: e.message };
    }
}

// Helper: Ambil Data Pemeliharaan / Perbaikan Berdasarkan Role (RBAC)
async function getMaintenanceByRole(user) {
    try {
        if (!user || user.normalized_role === 'mahasiswa' || user.normalized_role === 'dosen') {
            // Publik & Civitas Pelapor: hanya pemeliharaan yang berstatus tampil_publik = 1 (tanpa biaya)
            const [rows] = await dbPool.query(`
                SELECT p.kode_pemeliharaan, p.jenis_tindakan, p.tanggal_mulai, p.tanggal_selesai, p.status,
                       f.nama AS fasilitas, r.nama_ruangan, g.nama_gedung
                FROM pemeliharaan p
                JOIN fasilitas f ON p.fasilitas_id = f.id
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                WHERE p.tampil_publik = 1
                ORDER BY p.id DESC
                LIMIT 5
            `);
            return {
                isStaff: false,
                list: rows
            };
        }

        // Admin & Petugas Sarpras: melihat detail lengkap termasuk biaya dan teknisi
        const [rows] = await dbPool.query(`
            SELECT p.kode_pemeliharaan, p.jenis_tindakan, p.tanggal_mulai, p.tanggal_selesai, p.status, p.biaya,
                   u.nama_lengkap AS teknisi, f.nama AS fasilitas, r.nama_ruangan, g.nama_gedung
            FROM pemeliharaan p
            JOIN users u ON p.petugas_id = u.id
            JOIN fasilitas f ON p.fasilitas_id = f.id
            JOIN ruangan r ON f.ruangan_id = r.id
            JOIN gedung g ON r.gedung_id = g.id
            ORDER BY p.id DESC
            LIMIT 5
        `);
        return {
            isStaff: true,
            list: rows
        };
    } catch (e) {
        return { error: e.message };
    }
}

// Helper: Ambil Notifikasi Pengguna (RBAC)
async function getUserNotifications(user) {
    try {
        if (!user) return null;
        const [rows] = await dbPool.query(`
            SELECT judul, pesan, tipe, is_read, created_at
            FROM notifikasi
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT 5
        `, [user.id]);
        return rows;
    } catch (e) {
        return [];
    }
}

/**
 * Deterministic Local Matcher (Hemat Token, Zero-Hallucination, dan RBAC Grounding)
 * Memetakan intent pertanyaan ke query database aktual terverifikasi
 */
async function matchLocalIntent(cleanMessage, user, convSession) {
    const q = cleanMessage.toLowerCase().trim();

    // =========================================================================
    // 1. KEBIJAKAN KEAMANAN & LARANGAN KODE PROGRAM
    // =========================================================================
    if (isCodingRequest(cleanMessage)) {
        return {
            matched: true,
            intent: 'CODING_REQUEST_BLOCKED',
            source: 'security_policy',
            reply: CODE_REFUSAL_MESSAGE
        };
    }

    // =========================================================================
    // 2. CEGAH PERTANYAAN DI LUAR KONTEKS SISTEM
    // =========================================================================
    if (isOutOfContextRequest(cleanMessage)) {
        return {
            matched: true,
            intent: 'OUT_OF_CONTEXT_BLOCKED',
            source: 'security_policy',
            reply: OUT_OF_CONTEXT_MESSAGE
        };
    }

    // =========================================================================
    // 2b. KONTEKS PERCAKAPAN: Pertanyaan Lanjutan (Follow-Up Detection)
    //
    // Jika ada riwayat percakapan, periksa apakah pesan saat ini adalah
    // pertanyaan lanjutan yang merujuk pada jawaban/topik sebelumnya.
    // Ini menghindari penolakan "di luar konteks" untuk pertanyaan semacam:
    //   "99 itu fasilitas apa?", "4 itu ruangan apa?", "sebutkan fasilitasnya"
    // =========================================================================
    if (convSession && convSession.history.length > 0) {
        const lastBotMsg = convSession.history
            .filter(h => h.role === 'model')
            .slice(-1)[0]?.text || '';
        const lastUserMsg = convSession.history
            .filter(h => h.role === 'user')
            .slice(-1)[0]?.text?.toLowerCase() || '';

        // --- Pola referensi angka dari jawaban sebelumnya ---
        // Contoh: "99 itu fasilitas apa?", "4 itu ruangan apa?", "itu apa?"
        const followUpNumber = q.match(/^(\d+)\s*(itu|ini|tersebut)?\s*(fasilitas|inventaris|sarana|ruangan|gedung|item|unit|jenis|tipe|macam|apa|apa saja|itu apa)/i);

        // --- Pola "sebutkan X", "daftar X", "apa saja X" tanpa keyword utama ---
        const isFollowUpList =
            /\b(sebutkan|daftar|list|tampilkan|apa saja|mana saja|apa sajakah|rincian|jelaskan)\b/i.test(q) &&
            q.length < 60;

        // --- Pola "itu apa", "maksudnya apa", "artinya apa" ---
        const isFollowUpExplain =
            /\b(itu apa|itu fasilitas apa|itu ruangan apa|maksudnya|artinya|penjelasan|lebih lanjut|detail|rincian)\b/i.test(q);

        // Tentukan topik dari riwayat terakhir
        const lastTopicFasilitas =
            /fasilitas|inventaris|sarana|unit|jenis fasilitas|kondisi (baik|rusak)/i.test(lastBotMsg) ||
            /fasilitas|inventaris|sarana/i.test(lastUserMsg);
        const lastTopicRuangan =
            /ruangan|ruang|kelas|laboratorium/i.test(lastBotMsg) ||
            /ruangan|ruang|kelas|lab/i.test(lastUserMsg);
        const lastTopicGedung =
            /gedung/i.test(lastBotMsg) ||
            /gedung|bangunan/i.test(lastUserMsg);

        // Apakah jawaban sebelumnya adalah angka saja?
        const lastAnswerWasNumber = /^\d+$/.test(lastBotMsg.trim());

        // --- FOLLOW-UP: Fasilitas ---
        if (
            (followUpNumber && /fasilitas|inventaris|sarana|item|unit|jenis/i.test(followUpNumber[0])) ||
            (isFollowUpList && (lastTopicFasilitas || /fasilitas|inventaris|sarana/i.test(q))) ||
            (isFollowUpExplain && lastTopicFasilitas) ||
            (lastAnswerWasNumber && (
                /fasilitas|inventaris|sarana/i.test(q) ||
                (/itu|ini|tersebut/.test(q) && lastTopicFasilitas)
            ))
        ) {
            const stats = await getActualStats();
            if (!stats.connected) {
                return {
                    matched: true,
                    intent: 'FOLLOWUP_FACILITY_DB_ERROR',
                    source: 'local_fallback',
                    reply: DB_UNAVAILABLE_MESSAGE
                };
            }

            const facilityRows = await getFacilityList(50);
            if (facilityRows.length === 0) {
                return {
                    matched: true,
                    intent: 'FOLLOWUP_FACILITY_EMPTY',
                    source: 'database_actual',
                    reply: 'Belum ada data fasilitas yang terdata di sistem.'
                };
            }

            // Kelompokkan berdasarkan kategori
            const grouped = {};
            for (const f of facilityRows) {
                const kat = f.nama_kategori || 'Lainnya';
                if (!grouped[kat]) grouped[kat] = [];
                grouped[kat].push(f);
            }

            const listText = Object.entries(grouped).map(([kat, items]) => {
                const itemList = items.map(f => {
                    const kondisi = f.kondisi.replace(/_/g, ' ');
                    return `   • ${f.nama} (${f.jumlah} unit, ${kondisi}) — ${f.nama_ruangan}`;
                }).join('\n');
                return `**${kat}:**\n${itemList}`;
            }).join('\n\n');

            return {
                matched: true,
                intent: 'FOLLOWUP_FACILITY_LIST',
                source: 'database_actual',
                reply: `Berdasarkan data aktual database, berikut rincian **${stats.fasilitasItem} jenis fasilitas** (total ${stats.fasilitasUnit} unit fisik) di kampus STMIK IKMI Cirebon:\n\n${listText}`
            };
        }

        // --- FOLLOW-UP: Ruangan ---
        if (
            (followUpNumber && /ruangan|ruang|kelas|lab/i.test(followUpNumber[0])) ||
            (isFollowUpList && (lastTopicRuangan || /ruangan|ruang|kelas|lab/i.test(q))) ||
            (isFollowUpExplain && lastTopicRuangan) ||
            (lastAnswerWasNumber && (
                /ruangan|ruang|kelas|lab/i.test(q) ||
                (/itu|ini|tersebut/.test(q) && lastTopicRuangan)
            ))
        ) {
            const stats = await getActualStats();
            if (!stats.connected) {
                return {
                    matched: true,
                    intent: 'FOLLOWUP_ROOM_DB_ERROR',
                    source: 'local_fallback',
                    reply: DB_UNAVAILABLE_MESSAGE
                };
            }

            const rooms = await getRoomList(20);
            if (rooms.length === 0) {
                return {
                    matched: true,
                    intent: 'FOLLOWUP_ROOM_EMPTY',
                    source: 'database_actual',
                    reply: 'Belum ada data ruangan yang terdata di sistem.'
                };
            }

            const listText = rooms.map((r, i) =>
                `${i + 1}. **${r.nama_ruangan}** — ${r.jenis_ruangan} (${r.nama_gedung}, Lt. ${r.lantai})\n` +
                `   Status: *${r.status}* | Kapasitas: ${r.kapasitas} orang | Inventaris: ${r.total_item} jenis (${r.total_unit} unit)`
            ).join('\n\n');

            return {
                matched: true,
                intent: 'FOLLOWUP_ROOM_LIST',
                source: 'database_actual',
                reply: `Berikut daftar **${rooms.length} ruangan** yang terdata di kampus STMIK IKMI Cirebon:\n\n${listText}`
            };
        }

        // --- FOLLOW-UP: Gedung ---
        if (
            (isFollowUpList && (lastTopicGedung || /gedung|bangunan/i.test(q))) ||
            (isFollowUpExplain && lastTopicGedung) ||
            (lastAnswerWasNumber && (
                /gedung|bangunan/i.test(q) ||
                (/itu|ini|tersebut/.test(q) && lastTopicGedung)
            ))
        ) {
            const buildings = await getBuildingList();
            if (buildings.length === 0) {
                return {
                    matched: true,
                    intent: 'FOLLOWUP_BUILDING_EMPTY',
                    source: 'database_actual',
                    reply: 'Belum ada data gedung yang terdata di sistem.'
                };
            }

            const listText = buildings.map((b, i) =>
                `${i + 1}. **${b.nama_gedung}** (Kode: \`${b.kode_gedung}\`, ${b.jumlah_lantai} lantai)\n   Lokasi: ${b.lokasi}`
            ).join('\n\n');

            return {
                matched: true,
                intent: 'FOLLOWUP_BUILDING_LIST',
                source: 'database_actual',
                reply: `Berikut daftar gedung aktif di kampus STMIK IKMI Cirebon:\n\n${listText}`
            };
        }
    }

    // =========================================================================
    // 3. IDENTITAS BOT: "Siapa Anda?"
    // =========================================================================
    if (
        q === 'siapa anda?' || q === 'siapa anda' ||
        q === 'siapa kamu?' || q === 'siapa kamu' ||
        q.includes('siapa anda') || q.includes('kamu siapa') ||
        q === 'who are you' || q === 'who are you?'
    ) {
        const provDisplay = (AI_PROVIDER === 'openrouter') ? 'DeepSeek / OpenRouter' :
                            (AI_PROVIDER === 'deepseek') ? 'DeepSeek AI' :
                            (AI_PROVIDER === 'gemini') ? 'Google Gemini AI' :
                            (AI_PROVIDER === 'rule_engine') ? 'Rule-Based Engine (Lokal Kampus)' :
                            (AI_PROVIDER || 'Rule Engine');
        const modelDisplay = AI_MODEL || 'deepseek/deepseek-chat:free';
        return {
            matched: true,
            intent: 'IDENTITY_BOT',
            source: 'local',
            reply: `Halo! Saya adalah **Asisten Fasilitas IKMI** pada Sistem Informasi Pendataan Fasilitas Ruangan Kampus STMIK IKMI Cirebon.\n\n` +
                `Saat ini sistem percakapan saya ditenagai oleh model kecerdasan buatan: **${modelDisplay}** (${provDisplay}).\n\n` +
                `Tugas saya adalah membantu civitas akademika dalam memperoleh informasi resmi mengenai sarana dan prasarana kampus:\n` +
                `• Data dan ketersediaan ruangan di setiap gedung\n` +
                `• Inventaris fasilitas beserta kondisi fisiknya\n` +
                `• Prosedur pelaporan sarana prasarana yang memerlukan perbaikan\n` +
                `• Jadwal dan informasi pemeliharaan fasilitas.`
        };
    }

    // =========================================================================
    // 3b. PERTANYAAN MODEL AI: "Model AI Apa yang Kamu Gunakan?"
    // =========================================================================
    if (
        q.includes('model apa') || q.includes('pakai ai apa') || q.includes('menggunakan model') ||
        q.includes('pakai model') || q.includes('engine apa') || q.includes('deepseek') ||
        q.includes('gemini') || q.includes('provider ai') || q.includes('versi ai') ||
        q.includes('kamu pakai apa') || q.includes('teknologi ai')
    ) {
        const provDisplay = (AI_PROVIDER === 'openrouter') ? 'DeepSeek (via OpenRouter)' :
                            (AI_PROVIDER === 'deepseek') ? 'DeepSeek AI' :
                            (AI_PROVIDER === 'gemini') ? 'Google Gemini AI' :
                            (AI_PROVIDER === 'rule_engine') ? 'Rule-Based Engine (Lokal Kampus)' :
                            (AI_PROVIDER || 'Rule Engine');
        const modelDisplay = AI_MODEL || 'deepseek/deepseek-chat:free';
        return {
            matched: true,
            intent: 'IDENTITY_AI_MODEL',
            source: 'local',
            reply: `Asisten Fasilitas Kampus ini saat ini ditenagai oleh model kecerdasan buatan: **${modelDisplay}** dengan provider **${provDisplay}**.\n\n` +
                   `Model ini bertugas memproses pertanyaan bahasa natural Anda dan memadukannya dengan data faktual dari database MySQL kampus secara real-time.`
        };
    }

    // =========================================================================
    // 4. IDENTITAS PENGGUNA: "Siapa Saya?" (Session-Driven & Role-Aware)
    // =========================================================================
    if (
        q === 'siapa saya?' || q === 'siapa saya' ||
        q === 'siapa aku?' || q === 'siapa aku' ||
        q.includes('siapa saya') || q.includes('siapa diriku') ||
        q.includes('role saya') || q.includes('peran saya') ||
        q === 'apakah saya sudah login?' || q === 'siapa yang login' ||
        q === 'who am i' || q === 'who am i?'
    ) {
        // Kasus A: Pengguna belum login (Anonim)
        if (!user) {
            return {
                matched: true,
                intent: 'IDENTITY_USER_ANONYMOUS',
                source: 'local',
                reply: `Saat ini Anda mengakses halaman Beranda sebagai pengguna anonim. Saya belum dapat memastikan apakah Anda mahasiswa, dosen, petugas sarpras, atau administrator. Silakan masuk melalui menu Masuk Sistem agar saya dapat mengenali peran Anda berdasarkan sesi login yang valid.`
            };
        }

        // Kasus B: Pengguna telah login dengan sesi server yang terverifikasi
        let roleExplanation = '';
        if (user.normalized_role === 'mahasiswa') {
            roleExplanation = `Anda teridentifikasi sebagai mahasiswa pada Sistem Informasi Fasilitas STMIK IKMI Cirebon.`;
        } else if (user.normalized_role === 'dosen') {
            roleExplanation = `Anda teridentifikasi sebagai dosen pada Sistem Informasi Fasilitas STMIK IKMI Cirebon.`;
        } else if (user.normalized_role === 'petugas_sarpras') {
            roleExplanation = `Anda teridentifikasi sebagai petugas sarana dan prasarana pada Sistem Informasi Fasilitas STMIK IKMI Cirebon.`;
        } else if (user.normalized_role === 'admin') {
            roleExplanation = `Anda teridentifikasi sebagai administrator pada Sistem Informasi Fasilitas STMIK IKMI Cirebon.`;
        } else {
            roleExplanation = `Anda teridentifikasi sebagai ${user.role_display} pada Sistem Informasi Fasilitas STMIK IKMI Cirebon.`;
        }

        const idLabel = user.normalized_role === 'mahasiswa'
            ? 'NIM'
            : (user.normalized_role === 'dosen' ? 'NIDN/NIP' : 'Nomor Induk');

        const details =
            `\n\n• **Nama Lengkap:** ${user.nama_lengkap}\n` +
            `• **${idLabel}:** \`${user.nomor_induk}\`\n` +
            `• **Peran Sistem:** ${user.role_display.toUpperCase()}`;

        return {
            matched: true,
            intent: 'IDENTITY_USER_AUTHENTICATED',
            source: 'database_actual',
            reply: `${roleExplanation}${details}`
        };
    }

    // Deteksi apakah pengguna meminta rincian / daftar / penjelasan
    const wantsDetailOrList = /\b(daftar|list|jelaskan|sebutkan|rincian|rincikan|detail|tampilkan|apa saja|mana saja|apa sajakah)\b/i.test(q);

    // =========================================================================
    // 4b. FASILITAS PER-RUANGAN SPESIFIK
    // Contoh: "fasilitas di ruangan 201", "total unit fasilitas ruangan RK-201",
    //         "apa saja fasilitas ruang 201", "inventaris di kelas 201"
    // =========================================================================
    const roomKeywordMatch =
        q.match(/(?:ruang(?:an)?|kelas|lab(?:oratorium)?|rk)[- ]?(\w+[-]?\w*)/i) ||
        q.match(/(?:di|pada|dalam|untuk)\s+(?:ruang(?:an)?|kelas|lab)?\s*([A-Za-z0-9][-A-Za-z0-9]*)/i);

    const isFacilityPerRoom = (
        (q.includes('fasilitas') || q.includes('inventaris') || q.includes('sarana') || q.includes('unit') || q.includes('total unit')) &&
        (q.includes('ruang') || q.includes('kelas') || q.includes('lab')) &&
        roomKeywordMatch
    );

    if (isFacilityPerRoom) {
        // Ekstrak keyword ruangan dari query (misal: "201", "RK-201", "Lab Jaringan")
        let roomKeyword = '';
        // Coba ambil angka ruangan langsung
        const numMatch = q.match(/\b(\d{3,4}[A-Za-z]?)\b/);
        const rkMatch  = q.match(/\b(RK[-]?\d{3}[A-Za-z]?)\b/i);
        const labMatch = q.match(/\b(lab(?:oratorium)?\s+\w+)\b/i);
        // Jika RK-201 → cari dengan "RK-201" ataupun "201"
        if (rkMatch)       roomKeyword = rkMatch[1]; // simpan dengan tanda hubung: "RK-201"
        else if (numMatch) roomKeyword = numMatch[1];
        else if (labMatch) roomKeyword = labMatch[1];
        else               roomKeyword = roomKeywordMatch[1] || '';

        if (roomKeyword) {
            const roomInfos = await getRoomInfoByKeyword(roomKeyword);
            if (roomInfos.length === 0) {
                return {
                    matched: true,
                    intent: 'FACILITY_BY_ROOM_NOT_FOUND',
                    source: 'database_actual',
                    reply: `Maaf, ruangan dengan kata kunci **"${roomKeyword}"** tidak ditemukan di database kampus. Pastikan kode atau nama ruangan sudah benar (contoh: *201*, *RK-201*, *Lab Jaringan*).`
                };
            }

            const facilityRows = await getFacilitiesByRoom(roomKeyword);
            const r = roomInfos[0];
            const kondisiIcon = { baik: '✅', rusak_ringan: '⚠️', rusak_berat: '🔴', tidak_layak: '🔴', hilang: '❌' };

            let header = `📍 **${r.nama_ruangan}** (${r.kode_ruangan})\n` +
                         `• Gedung: ${r.nama_gedung} | Lantai: ${r.lantai} | Jenis: ${r.jenis_ruangan || '-'}\n` +
                         `• Total Item Fasilitas: **${r.total_item}** jenis | Total Unit: **${r.total_unit}** unit\n` +
                         `• Unit Baik: ${r.unit_baik} | Unit Bermasalah: ${r.unit_bermasalah}\n\n`;

            if (facilityRows.length === 0) {
                return {
                    matched: true,
                    intent: 'FACILITY_BY_ROOM_EMPTY',
                    source: 'database_actual',
                    reply: header + `Belum ada data fasilitas yang terdaftar di ruangan ini.`
                };
            }

            // Kelompokkan per kategori
            const byCategory = {};
            for (const f of facilityRows) {
                const cat = f.nama_kategori || 'Lainnya';
                if (!byCategory[cat]) byCategory[cat] = [];
                byCategory[cat].push(f);
            }

            let detail = `**Rincian Fasilitas:**\n\n`;
            for (const [cat, items] of Object.entries(byCategory)) {
                detail += `**${cat}:**\n`;
                for (const f of items) {
                    const icon = kondisiIcon[f.kondisi] || '•';
                    const ketNote = f.keterangan ? ` _(${f.keterangan})_` : '';
                    detail += `  ${icon} ${f.nama} — **${f.jumlah} unit** (${f.kondisi.replace('_',' ')})${ketNote}\n`;
                    detail += `     Kode Inventaris: \`${f.kode_inventaris}\`\n`;
                }
                detail += '\n';
            }

            return {
                matched: true,
                intent: 'FACILITY_BY_ROOM_DETAIL',
                source: 'database_actual',
                reply: header + detail.trim()
            };
        }
    }

    // =========================================================================
    // 5. DATA AKTUAL: Penjelasan / Rincian Fasilitas Yang Tersedia
    // (Misalnya: "Jelaskan fasilitas yang tersedia.", "Tolong jelaskan fasilitas yang tersedia.")
    // =========================================================================
    const isExplainAvailableFacilities = (
        (q.includes('fasilitas') || q.includes('inventaris') || q.includes('sarana')) &&
        (wantsDetailOrList || q.includes('tersedia')) &&
        !q.includes('berapa') && !q.includes('jumlah') && !q.includes('total') && !q.includes('hitung') &&
        !(q.includes('ruang') && !q.includes('fasilitas'))
    );

    if (isExplainAvailableFacilities) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'FACILITY_AVAILABLE_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        const [facilityRows] = await dbPool.query(`
            SELECT f.nama, f.kode_inventaris, f.jumlah, f.kondisi, f.keterangan,
                   r.nama_ruangan, r.kode_ruangan, r.lantai, g.nama_gedung
            FROM fasilitas f
            JOIN ruangan r ON f.ruangan_id = r.id
            JOIN gedung g ON r.gedung_id = g.id
            WHERE r.status = 'tersedia'
            ORDER BY g.nama_gedung ASC, r.nama_ruangan ASC, f.nama ASC
        `);

        if (facilityRows.length === 0) {
            return {
                matched: true,
                intent: 'FACILITY_AVAILABLE_EMPTY',
                source: 'database_actual',
                reply: 'Saat ini belum ada data fasilitas yang berstatus tersedia di database.'
            };
        }

        const listText = facilityRows.map((f, i) => {
            const kondisiText = f.kondisi.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
            const cleanKet = f.keterangan ? f.keterangan.split('\n')[0].trim() : '';
            const ketText = cleanKet ? `\n   • Keterangan: ${cleanKet}` : '';
            return `${i + 1}. **${f.nama}** (${f.jumlah} unit, kondisi ${kondisiText})\n` +
                `   • Lokasi: ${f.nama_ruangan} (${f.nama_gedung}, Lt. ${f.lantai})${ketText}`;
        }).join('\n\n');

        return {
            matched: true,
            intent: 'FACILITY_AVAILABLE_EXPLANATION',
            source: 'database_actual',
            reply: `Berikut penjelasan fasilitas yang tersedia di kampus STMIK IKMI Cirebon (total ${facilityRows.length} jenis inventaris, akumulasi ${stats.fasilitasUnit} unit fisik):\n\n${listText}`
        };
    }

    // =========================================================================
    // 6. DATA AKTUAL: Ruangan Dalam Perbaikan / Pemeliharaan
    // =========================================================================
    const isRuanganPerbaikan = (
        q.includes('ruang') || q.includes('lab')
    ) && (
            q.includes('perbaikan') || q.includes('diperbaiki') || q.includes('pemeliharaan')
        );

    if (isRuanganPerbaikan) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'ROOM_REPAIR_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        if (wantsDetailOrList && stats.ruanganPerbaikan > 0) {
            const [repairRooms] = await dbPool.query(`
                SELECT r.nama_ruangan, r.kode_ruangan, r.lantai, g.nama_gedung
                FROM ruangan r
                JOIN gedung g ON r.gedung_id = g.id
                WHERE r.status = 'perbaikan'
                ORDER BY r.nama_ruangan ASC
            `);
            const listText = repairRooms.map((r, i) => `${i + 1}. **${r.nama_ruangan}** (${r.nama_gedung}, Lt. ${r.lantai})`).join('\n');
            return {
                matched: true,
                intent: 'ROOM_REPAIR_LIST',
                source: 'database_actual',
                reply: `Berikut ruangan yang sedang diperbaiki (total ${repairRooms.length} ruangan):\n\n${listText}`
            };
        }

        return {
            matched: true,
            intent: 'ROOM_REPAIR_COUNT',
            source: 'database_actual',
            reply: `${stats.ruanganPerbaikan}`
        };
    }

    // =========================================================================
    // 7. DATA AKTUAL: Ruangan Tersedia
    // =========================================================================
    const isRuanganTersedia = (
        (q.includes('ruang') || q.includes('lab') || q.includes('kelas')) &&
        (q.includes('tersedia') || q.includes('ketersediaan') || q.includes('siap pakai') || q.includes('kosong'))
    );

    if (isRuanganTersedia) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'ROOM_AVAILABLE_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        if (wantsDetailOrList) {
            const rooms = await getAvailableRoomList(20);
            if (rooms.length === 0) {
                return {
                    matched: true,
                    intent: 'AVAILABLE_ROOMS_EMPTY',
                    source: 'database_actual',
                    reply: 'Saat ini belum ada ruangan yang berstatus tersedia di database.'
                };
            }

            const listText = rooms.map((r, i) =>
                `${i + 1}. **${r.nama_ruangan}** (${r.nama_gedung}, Lt. ${r.lantai})\n` +
                `   • Jenis: ${r.jenis_ruangan} | Kapasitas: ${r.kapasitas} orang\n` +
                `   • Status: *${r.status}* (${r.total_item} jenis fasilitas, ${r.total_unit} unit)`
            ).join('\n\n');

            return {
                matched: true,
                intent: 'AVAILABLE_ROOM_LIST',
                source: 'database_actual',
                reply: `Berikut daftar ruangan yang tersedia di kampus STMIK IKMI Cirebon (total ${rooms.length} ruangan):\n\n${listText}`
            };
        }

        return {
            matched: true,
            intent: 'ROOM_AVAILABLE_COUNT',
            source: 'database_actual',
            reply: `${stats.ruanganTersedia}`
        };
    }

    // =========================================================================
    // 8. DATA AKTUAL: Ruangan Digunakan
    // =========================================================================
    const isRuanganDigunakan = (
        (q.includes('ruang') || q.includes('lab') || q.includes('kelas')) &&
        (q.includes('digunakan') || q.includes('dipakai') || q.includes('terpakai'))
    );

    if (isRuanganDigunakan) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'ROOM_USED_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        return {
            matched: true,
            intent: 'ROOM_USED_COUNT',
            source: 'database_actual',
            reply: `${stats.ruanganDigunakan}`
        };
    }

    // =========================================================================
    // 9. DATA AKTUAL: Jumlah Ruangan Kampus & Daftar Ruangan
    // =========================================================================
    const isRuanganGeneral = (
        (q.includes('ruang') || q.includes('lab') || q.includes('kelas')) &&
        (
            q.includes('jumlah') || q.includes('berapa') || q.includes('total') ||
            q.includes('banyak') || q.includes('terdata') || q.includes('di kampus') ||
            q.includes('kampus') || wantsDetailOrList
        )
    ) || (
            q === 'ada berapa ruangan?' || q === 'ada berapa ruangan' ||
            q === 'berapa ruangan?' || q === 'berapa ruangan' ||
            q === 'jumlah ruangan' || q === 'total ruangan'
        );

    if (isRuanganGeneral) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'ROOM_COUNT_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        if (wantsDetailOrList) {
            const rooms = await getRoomList(20);
            if (rooms.length === 0) {
                return {
                    matched: true,
                    intent: 'ROOM_LIST_EMPTY',
                    source: 'database_actual',
                    reply: 'Belum ada data ruangan yang terdata di sistem.'
                };
            }

            const listText = rooms.map((r, i) =>
                `${i + 1}. **${r.nama_ruangan}** (${r.nama_gedung}, Lt. ${r.lantai})\n` +
                `   • Jenis: ${r.jenis_ruangan} | Status: *${r.status}*\n` +
                `   • Inventaris: ${r.total_item} jenis fasilitas (${r.total_unit} unit)`
            ).join('\n\n');

            return {
                matched: true,
                intent: 'ROOM_LIST',
                source: 'database_actual',
                reply: `Berikut daftar ruangan yang terdata di kampus STMIK IKMI Cirebon (total ${rooms.length} ruangan):\n\n${listText}`
            };
        }

        return {
            matched: true,
            intent: 'ROOM_COUNT',
            source: 'database_actual',
            reply: `${stats.ruanganTotal}`
        };
    }

    // =========================================================================
    // 10. DATA AKTUAL: Jenis Fasilitas
    // =========================================================================
    const isJenisFasilitas = (
        q.includes('jenis fasilitas') || q.includes('macam fasilitas') ||
        q.includes('jenis inventaris') || q.includes('macam inventaris') ||
        (
            (q.includes('fasilitas') || q.includes('inventaris')) &&
            (q.includes('jenis') || q.includes('macam') || q.includes('tipe')) &&
            (q.includes('berapa') || q.includes('jumlah') || q.includes('total') || q.includes('ada berapa'))
        )
    );

    if (isJenisFasilitas) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'FACILITY_TYPE_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        if (wantsDetailOrList) {
            const [rows] = await dbPool.query(`
                SELECT f.nama, f.kode_inventaris, f.jumlah, f.kondisi, r.nama_ruangan, g.nama_gedung
                FROM fasilitas f
                JOIN ruangan r ON f.ruangan_id = r.id
                JOIN gedung g ON r.gedung_id = g.id
                ORDER BY f.nama ASC
            `);
            const listText = rows.map((f, i) => `${i + 1}. **${f.nama}** (${f.jumlah} unit, kondisi ${f.kondisi.replace('_', ' ')}) — Lokasi: ${f.nama_ruangan}`).join('\n');
            return {
                matched: true,
                intent: 'FACILITY_TYPE_LIST',
                source: 'database_actual',
                reply: `Berikut daftar ${rows.length} jenis fasilitas yang terdata di STMIK IKMI Cirebon:\n\n${listText}`
            };
        }

        return {
            matched: true,
            intent: 'FACILITY_TYPE_COUNT',
            source: 'database_actual',
            reply: `${stats.fasilitasItem}`
        };
    }

    // =========================================================================
    // 11. DATA AKTUAL: Total Fasilitas (Unit Fisik)
    // =========================================================================
    const isTotalFasilitasUnit = (
        (
            (q.includes('fasilitas') || q.includes('inventaris') || q.includes('sarana')) &&
            (
                q.includes('total') || q.includes('jumlah') || q.includes('banyak') ||
                q.includes('unit') || q.includes('terdata') || q.includes('di kampus') ||
                q.includes('kampus') || q.includes('seluruh') || q.includes('semua') ||
                q.includes('berapa') || q.includes('ada berapa') || wantsDetailOrList
            )
        ) || (
            q === 'ada berapa fasilitas?' || q === 'ada berapa fasilitas' ||
            q === 'berapa fasilitas?' || q === 'berapa fasilitas' ||
            q === 'total fasilitas' || q === 'jumlah fasilitas' ||
            q === 'total unit fasilitas' || q === 'jumlah unit fasilitas'
        )
    ) && !q.includes('kategori fasilitas') && !q.includes('jenis') && !q.includes('macam');

    if (isTotalFasilitasUnit) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'FACILITY_COUNT_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        if (wantsDetailOrList) {
            return {
                matched: true,
                intent: 'FACILITY_DETAIL',
                source: 'database_actual',
                reply: `Berdasarkan data aktual pada database STMIK IKMI Cirebon:\n\n` +
                    `• **Total Jenis Fasilitas:** **${stats.fasilitasItem} jenis fasilitas**\n` +
                    `• **Total Akumulasi Fisik:** **${stats.fasilitasUnit} unit** sarana prasarana\n\n` +
                    `Kondisi fisik fasilitas saat ini:\n` +
                    `• Kondisi Baik: **${stats.fasilitasBaik} item**\n` +
                    `• Rusak Ringan: **${stats.fasilitasRusakRingan} item**\n` +
                    `• Rusak Berat: **${stats.fasilitasRusakBerat} item**\n` +
                    `• Tidak Layak Pakai: **${stats.fasilitasTidakLayak} item**`
            };
        }

        return {
            matched: true,
            intent: 'FACILITY_COUNT',
            source: 'database_actual',
            reply: `${stats.fasilitasUnit}`
        };
    }

    // =========================================================================
    // 12. DATA AKTUAL: Jumlah Gedung Kampus
    // =========================================================================
    const isGedungGeneral = (
        q.includes('jumlah gedung') || q.includes('berapa gedung') ||
        q.includes('total gedung') || q.includes('gedung di kampus') ||
        q.includes('gedung kampus') ||
        ((q.includes('gedung') || q.includes('bangunan')) && (q.includes('berapa') || q.includes('jumlah') || q.includes('total') || q.includes('ada berapa')))
    );

    if (isGedungGeneral) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'BUILDING_COUNT_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        if (wantsDetailOrList) {
            const buildings = await getBuildingList();
            let listText = '';
            if (buildings.length > 0) {
                listText = `\n\nDaftar gedung kampus aktif:\n` +
                    buildings.map((b, i) => `${i + 1}. **${b.nama_gedung}** (Kode: \`${b.kode_gedung}\`, ${b.jumlah_lantai} Lantai)\n   *Lokasi:* ${b.lokasi}`).join('\n');
            }

            return {
                matched: true,
                intent: 'BUILDING_LIST',
                source: 'database_actual',
                reply: `Berdasarkan data aktual pada database STMIK IKMI Cirebon, terdapat **${stats.gedung} gedung kampus** yang aktif.${listText}`
            };
        }

        return {
            matched: true,
            intent: 'BUILDING_COUNT',
            source: 'database_actual',
            reply: `${stats.gedung}`
        };
    }

    // =========================================================================
    // 13. DATA AKTUAL: Kategori Fasilitas
    // =========================================================================
    if (q.includes('kategori fasilitas') || q.includes('kelompok fasilitas')) {
        const categories = await getCategoryList();
        if (categories.length === 0) {
            return {
                matched: true,
                intent: 'CATEGORY_LIST_EMPTY',
                source: 'database_actual',
                reply: `Informasi kategori fasilitas belum tersedia di database.`
            };
        }

        if (!wantsDetailOrList && (q.includes('berapa') || q.includes('jumlah') || q.includes('total') || q.includes('ada berapa'))) {
            return {
                matched: true,
                intent: 'CATEGORY_COUNT',
                source: 'database_actual',
                reply: `${categories.length}`
            };
        }

        const listText = categories.map((c, i) => `${i + 1}. **${c.nama_kategori}**: ${c.total_item} jenis (${c.total_unit} unit)`).join('\n');
        return {
            matched: true,
            intent: 'CATEGORY_LIST',
            source: 'database_actual',
            reply: `Berikut adalah kategori fasilitas yang terdata di STMIK IKMI Cirebon:\n\n${listText}`
        };
    }

    // =========================================================================
    // 14. DATA AKTUAL: Fasilitas Rusak / Kondisi Rusak
    // =========================================================================
    if (q.includes('rusak berat') || q.includes('fasilitas rusak') || q.includes('rusak ringan') || ((q.includes('kondisi baik') || q.includes('fasilitas baik')) && (q.includes('berapa') || q.includes('jumlah')))) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'DAMAGED_QUERY_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        // Jawaban angka saja untuk pertanyaan jumlah kondisi
        if (!wantsDetailOrList && (q.includes('berapa') || q.includes('jumlah') || q.includes('total') || q.includes('ada berapa'))) {
            if (q.includes('rusak berat')) {
                return {
                    matched: true,
                    intent: 'DAMAGED_COUNT',
                    source: 'database_actual',
                    reply: `${stats.fasilitasRusakBerat}`
                };
            }
            if (q.includes('rusak ringan')) {
                return {
                    matched: true,
                    intent: 'DAMAGED_COUNT',
                    source: 'database_actual',
                    reply: `${stats.fasilitasRusakRingan}`
                };
            }
            if (q.includes('baik')) {
                return {
                    matched: true,
                    intent: 'GOOD_CONDITION_COUNT',
                    source: 'database_actual',
                    reply: `${stats.fasilitasBaik}`
                };
            }
            const totalRusak = stats.fasilitasRusakBerat + stats.fasilitasRusakRingan + stats.fasilitasTidakLayak;
            return {
                matched: true,
                intent: 'DAMAGED_COUNT',
                source: 'database_actual',
                reply: `${totalRusak}`
            };
        }

        const damaged = await getDamagedFacilities(6);
        if (damaged.length === 0) {
            return {
                matched: true,
                intent: 'DAMAGED_FACILITIES_NONE',
                source: 'database_actual',
                reply: `Berdasarkan data terkini di database kampus STMIK IKMI Cirebon, saat ini **tidak ada fasilitas yang berstatus rusak**. Seluruh sarana terpantau dalam kondisi baik.`
            };
        }

        const listText = damaged.map((f, i) =>
            `${i + 1}. **${f.nama}** (${f.jumlah} unit, Kode: \`${f.kode_inventaris}\`)\n   Lokasi: ${f.nama_ruangan} (${f.nama_gedung}) — Kondisi: *${f.kondisi.replace('_', ' ')}*`
        ).join('\n');

        return {
            matched: true,
            intent: 'DAMAGED_FACILITIES',
            source: 'database_actual',
            reply: `Berikut daftar fasilitas dengan kondisi memerlukan perhatian / perbaikan berdasarkan data aktual database:\n\n${listText}\n\n*Catatan: Penanganan dikoordinasikan oleh tim Sarpras melalui modul pemeliharaan.*`
        };
    }


    // =========================================================================
    // 10. LAPORAN KERUSAKAN (Role-Based Access Control)
    // =========================================================================
    if (
        q.includes('laporan kerusakan') || q.includes('tiket kerusakan') ||
        q.includes('lapor kerusakan') || q.includes('status laporan') ||
        q === 'laporan'
    ) {
        // Cek jika menanyakan prosedur pembuatan laporan
        if (q.includes('cara') || q.includes('prosedur') || q.includes('bagaimana')) {
            return {
                matched: true,
                intent: 'REPORT_PROCEDURE',
                source: 'local_guidance',
                reply: `**Prosedur Pelaporan Kerusakan Fasilitas STMIK IKMI Cirebon:**\n\n` +
                    `1. Masuk ke sistem melalui menu **Masuk Sistem** (Dosen/Mahasiswa/Staf).\n` +
                    `2. Buka menu **Laporan Kerusakan** > **Buat Laporan**.\n` +
                    `3. Pilih ruangan dan fasilitas yang mengalami kendala.\n` +
                    `4. Tuliskan deskripsi kerusakan secara jelas serta tingkat prioritasnya.\n` +
                    `5. Unggah foto bukti kerusakan jika tersedia, lalu kirim tiket laporan.\n` +
                    `6. Tim Petugas Sarpras akan memverifikasi dan menjadwalkan tindakan perbaikan.`
            };
        }

        const reportData = await getReportsByRole(user);
        if (reportData.error) {
            return {
                matched: true,
                intent: 'REPORT_QUERY_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        // Anonim: Hanya ringkasan publik & ajakan login
        if (reportData.type === 'anonymous') {
            return {
                matched: true,
                intent: 'REPORT_ANONYMOUS',
                source: 'database_actual',
                reply: `Informasi rincian tiket pelaporan kerusakan bersifat terbatas untuk civitas akademika terdaftar.\n\n` +
                    `• Berdasarkan database, saat ini terdapat **${reportData.total} tiket laporan kerusakan** terdaftar (dengan **${reportData.pending} tiket** dalam penanganan).\n\n` +
                    `Silakan masuk melalui menu **Masuk Sistem** untuk melihat status tiket laporan Anda atau membuat pelaporan baru.`
            };
        }

        // Pengguna (Mahasiswa/Dosen): Tiket milik sendiri
        if (reportData.type === 'user') {
            if (!reportData.list || reportData.list.length === 0) {
                return {
                    matched: true,
                    intent: 'REPORT_USER_EMPTY',
                    source: 'database_actual',
                    reply: `Halo **${user.nama_lengkap}**, saat ini Anda belum memiliki tiket laporan kerusakan yang tercatat di database sistem.\n\n` +
                        `Jika Anda menemukan sarana kampus yang rusak, Anda dapat mengajukan laporan melalui menu **Laporan Kerusakan**.`
                };
            }

            const listText = reportData.list.map((r, i) =>
                `${i + 1}. **Kode: \`${r.kode_laporan}\`** — ${r.fasilitas} (${r.nama_ruangan})\n   Prioritas: *${r.prioritas}* | Status: **${r.status.toUpperCase()}**\n   Keterangan: ${r.deskripsi}`
            ).join('\n\n');

            return {
                matched: true,
                intent: 'REPORT_USER_LIST',
                source: 'database_actual',
                reply: `Halo **${user.nama_lengkap}**, berikut daftar tiket laporan kerusakan fasilitas Anda:\n\n${listText}`
            };
        }

        // Admin & Petugas Sarpras: Monitoring seluruh tiket
        if (reportData.type === 'staff') {
            let listText = '';
            if (reportData.list && reportData.list.length > 0) {
                listText = '\n\nTiket laporan terbaru:\n' + reportData.list.map((r, i) =>
                    `${i + 1}. **\`${r.kode_laporan}\`** — ${r.fasilitas} (${r.nama_ruangan})\n   Pelapor: ${r.pelapor} | Status: **${r.status.toUpperCase()}** (*${r.prioritas}*)`
                ).join('\n');
            }

            return {
                matched: true,
                intent: 'REPORT_STAFF_LIST',
                source: 'database_actual',
                reply: `**Monitoring Laporan Kerusakan (Akses ${user.role_display.toUpperCase()}):**\n\n` +
                    `• Total Laporan: **${reportData.total} tiket**\n` +
                    `• Menunggu Verifikasi: **${reportData.menunggu} tiket**\n` +
                    `• Sedang Diproses: **${reportData.diproses} tiket**\n` +
                    `• Selesai Ditangani: **${reportData.selesai} tiket**${listText}`
            };
        }
    }

    // =========================================================================
    // 11. PEMELIHARAAN & PERBAIKAN (Role-Based Access Control)
    // =========================================================================
    if (
        q.includes('pemeliharaan') || q.includes('perbaikan') ||
        q.includes('jadwal perbaikan') || q.includes('maintenance') ||
        q.includes('servis fasilitas')
    ) {
        const mtData = await getMaintenanceByRole(user);
        if (mtData.error) {
            return {
                matched: true,
                intent: 'MAINTENANCE_QUERY_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        if (!mtData.list || mtData.list.length === 0) {
            return {
                matched: true,
                intent: 'MAINTENANCE_EMPTY',
                source: 'database_actual',
                reply: `Saat ini belum ada jadwal pemeliharaan fasilitas aktif yang tercatat di database.`
            };
        }

        // Untuk Publik, Mahasiswa, dan Dosen: Tampilkan jadwal tanpa biaya rahasia
        if (!mtData.isStaff) {
            const listText = mtData.list.map((p, i) =>
                `${i + 1}. **${p.fasilitas}** (${p.nama_ruangan}, ${p.nama_gedung})\n   Tindakan: *${p.jenis_tindakan}* | Mulai: ${p.tanggal_mulai} | Status: **${p.status.toUpperCase()}**`
            ).join('\n\n');

            return {
                matched: true,
                intent: 'MAINTENANCE_PUBLIC',
                source: 'database_actual',
                reply: `Berikut jadwal dan status kegiatan pemeliharaan sarana prasarana kampus STMIK IKMI Cirebon:\n\n${listText}`
            };
        }

        // Untuk Petugas Sarpras & Admin: Tampilkan detail operasional lengkap
        const listText = mtData.list.map((p, i) => {
            const biayaFormat = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(p.biaya || 0);
            return `${i + 1}. **\`${p.kode_pemeliharaan}\`** — ${p.fasilitas} (${p.nama_ruangan})\n   Tindakan: *${p.jenis_tindakan}* | Teknisi: ${p.teknisi}\n   Biaya: ${biayaFormat} | Status: **${p.status.toUpperCase()}**`;
        }).join('\n\n');

        return {
            matched: true,
            intent: 'MAINTENANCE_STAFF',
            source: 'database_actual',
            reply: `**Data Pemeliharaan Fasilitas Kampus (Akses ${user.role_display.toUpperCase()}):**\n\n${listText}`
        };
    }

    // =========================================================================
    // 12. NOTIFIKASI PENGGUNA (Role-Based Access Control)
    // =========================================================================
    if (q.includes('notifikasi') || q.includes('pemberitahuan') || q.includes('pesan saya')) {
        if (!user) {
            return {
                matched: true,
                intent: 'NOTIFICATIONS_ANONYMOUS',
                source: 'local',
                reply: `Notifikasi bersifat pribadi untuk setiap pengguna sistem. Silakan masuk melalui menu **Masuk Sistem** untuk melihat pemberitahuan terkini Anda.`
            };
        }

        const notifs = await getUserNotifications(user);
        if (!notifs || notifs.length === 0) {
            return {
                matched: true,
                intent: 'NOTIFICATIONS_EMPTY',
                source: 'database_actual',
                reply: `Halo **${user.nama_lengkap}**, tidak ada notifikasi baru untuk akun Anda saat ini.`
            };
        }

        const listText = notifs.map((n, i) =>
            `${i + 1}. **${n.judul}**\n   ${n.pesan} *(Status: ${n.is_read ? 'Sudah dibaca' : 'Belum dibaca'})*`
        ).join('\n\n');

        return {
            matched: true,
            intent: 'NOTIFICATIONS_LIST',
            source: 'database_actual',
            reply: `Halo **${user.nama_lengkap}**, berikut notifikasi terkini akun Anda:\n\n${listText}`
        };
    }

    // =========================================================================
    // 13. DATA AKTUAL: Jumlah Akun Pengguna Kampus
    // =========================================================================
    if (
        q.includes('jumlah akun') || q.includes('jumlah user') || q.includes('jumlah pengguna') ||
        q.includes('berapa user') || q.includes('berapa akun') || q.includes('berapa pengguna') ||
        q.includes('akun pengguna') || q.includes('total user') || q.includes('total akun') ||
        q === 'ada berapa jumlah akun pengguna?' || q === 'ada berapa jumlah akun pengguna'
    ) {
        const stats = await getActualStats();
        if (!stats.connected) {
            return {
                matched: true,
                intent: 'USER_COUNT_ERROR',
                source: 'local_fallback',
                reply: DB_UNAVAILABLE_MESSAGE
            };
        }

        return {
            matched: true,
            intent: 'USER_COUNT',
            source: 'database_actual',
            reply: `Berdasarkan data aktual pada database kampus STMIK IKMI Cirebon:\n\n` +
                `• **Total Akun Pengguna:** **${stats.usersTotal} akun terdaftar** (Seluruhnya aktif)\n` +
                `• **Administrator:** **${stats.usersAdmin} akun** (Pengelola sistem sarpras penuh)\n` +
                `• **Petugas Sarpras:** **${stats.usersPetugas} akun** (Teknisi dan tim pemeliharaan)\n` +
                `• **Dosen Civitas:** **${stats.usersDosen} akun** (Tenaga pendidik pelapor fasilitas)\n` +
                `• **Mahasiswa:** **${stats.usersMahasiswa} akun** (Civitas mahasiswa pelapor)`
        };
    }

    // =========================================================================
    // 13b. DATA AKTUAL: Pencarian Fasilitas Berdasarkan Nama Spesifik
    // (mis. "berapa jumlah kursi?", "berapa proyektor?", "ada berapa meja?")
    // =========================================================================
    const specificItemMatch = q.match(
        /\b(berapa|jumlah|total|ada berapa|hitung)\b.*?\b([a-z\s]{3,30}?)\s*\??\s*$/i
    );
    const isSpecificFacilityQuery = (
        (q.includes('berapa') || q.includes('jumlah') || q.includes('total') || q.includes('ada berapa')) &&
        !q.includes('fasilitas') && !q.includes('ruangan') && !q.includes('gedung') &&
        !q.includes('inventaris') && !q.includes('laporan') && !q.includes('pemeliharaan') &&
        !q.includes('akun') && !q.includes('user') && !q.includes('pengguna')
    );

    if (isSpecificFacilityQuery && specificItemMatch) {
        // Ekstrak kata kunci nama fasilitas dari kalimat
        const rawKeyword = specificItemMatch[2]?.trim().replace(/[^a-z\s]/gi, '').trim();
        if (rawKeyword && rawKeyword.length >= 3) {
            try {
                const [rows] = await dbPool.query(`
                    SELECT f.nama, f.jumlah, f.kondisi, r.nama_ruangan, g.nama_gedung
                    FROM fasilitas f
                    JOIN ruangan r ON f.ruangan_id = r.id
                    JOIN gedung g ON r.gedung_id = g.id
                    WHERE LOWER(f.nama) LIKE ?
                    ORDER BY f.nama ASC
                    LIMIT 20
                `, [`%${rawKeyword.toLowerCase()}%`]);

                if (rows.length > 0) {
                    const totalUnit = rows.reduce((sum, r) => sum + (parseInt(r.jumlah, 10) || 0), 0);

                    if (!wantsDetailOrList) {
                        // Hanya jumlah
                        return {
                            matched: true,
                            intent: 'SPECIFIC_FACILITY_COUNT',
                            source: 'database_actual',
                            reply: `${totalUnit}`
                        };
                    }

                    const listText = rows.map((f, i) => {
                        const kondisi = f.kondisi.replace(/_/g, ' ');
                        return `${i + 1}. **${f.nama}** — ${f.jumlah} unit (${kondisi})\n   Lokasi: ${f.nama_ruangan} (${f.nama_gedung})`;
                    }).join('\n\n');

                    return {
                        matched: true,
                        intent: 'SPECIFIC_FACILITY_LIST',
                        source: 'database_actual',
                        reply: `Berdasarkan pencarian kata kunci "**${rawKeyword}**" di database:\n\n${listText}\n\n*Total: ${totalUnit} unit*`
                    };
                } else {
                    // Tidak ditemukan — informasikan & tawarkan daftar semua fasilitas
                    const [allFac] = await dbPool.query(`SELECT nama FROM fasilitas ORDER BY nama ASC LIMIT 10`);
                    const namaList = allFac.map(f => `• ${f.nama}`).join('\n');
                    return {
                        matched: true,
                        intent: 'SPECIFIC_FACILITY_NOT_FOUND',
                        source: 'database_actual',
                        reply: `Fasilitas dengan nama mengandung "**${rawKeyword}**" tidak ditemukan di database.\n\nContoh fasilitas yang terdata:\n${namaList}\n\nAnda bisa bertanya "Sebutkan semua fasilitas" untuk melihat daftar lengkapnya.`
                    };
                }
            } catch (e) {
                // Biarkan jatuh ke AI fallback jika query gagal
            }
        }
    }

    // =========================================================================
    // 14. KOREKSI KONTEKS: Pertanyaan "Customer"
    // =========================================================================
    if (q.includes('customer') || q.includes('pelanggan') || q.includes('konsumen')) {
        return {
            matched: true,
            intent: 'CUSTOMER_QUERY_CORRECTION',
            source: 'local',
            reply: `Dalam **Sistem Informasi Pendataan Fasilitas Ruangan Kampus STMIK IKMI Cirebon**, data **"customer"** bukan merupakan kategori data sistem.\n\n` +
                `Sistem ini adalah platform manajemen sarana prasarana civitas akademika (mahasiswa, dosen, petugas sarpras, dan administrator).\n\n` +
                `Silakan tanyakan data resmi yang terdata di sistem, misalnya:\n` +
                `• *"Ada berapa jumlah ruangan?"*\n` +
                `• *"Ada berapa jumlah fasilitas?"*\n` +
                `• *"Berapa jumlah gedung kampus?"*`
        };
    }

    return { matched: false };
}

/**
 * Fallback AI Provider: Google Gemini / NVIDIA NIM
 * Hanya dipanggil untuk pertanyaan fasilitas yang memerlukan penyusunan bahasa natural,
 * dengan pembatasan ketat grounding database dan penolakan topik luar.
 * Parameter convHistory: array {role, text} dari riwayat percakapan sesi aktif.
 */
async function callAIProvider(userMessage, actualStats, user, convHistory) {
    if (!AI_API_KEY || AI_PROVIDER === 'rule_engine') {
        return null;
    }

    const userInfoStr = user
        ? `Pengguna teridentifikasi: ${user.nama_lengkap} (Peran: ${user.role_display}). `
        : `Pengguna belum login (Anonim). `;

    const systemInstruction =
        `Anda adalah Asisten Fasilitas IKMI untuk Sistem Informasi Pendataan Fasilitas Ruangan Kampus STMIK IKMI Cirebon. ` +
        `Konteks data aktual kampus saat ini dari MySQL:\n` +
        `- Total gedung: ${actualStats.gedung}\n` +
        `- Total ruangan: ${actualStats.ruanganTotal} (tersedia: ${actualStats.ruanganTersedia}, perbaikan: ${actualStats.ruanganPerbaikan}, digunakan: ${actualStats.ruanganDigunakan})\n` +
        `- Total fasilitas fisik: ${actualStats.fasilitasUnit} unit fisik\n` +
        `- Total jenis fasilitas: ${actualStats.fasilitasItem} jenis fasilitas\n` +
        `- Kondisi fasilitas: baik ${actualStats.fasilitasBaik}, rusak ringan ${actualStats.fasilitasRusakRingan}, rusak berat ${actualStats.fasilitasRusakBerat}, tidak layak ${actualStats.fasilitasTidakLayak}\n` +
        `- ${userInfoStr}\n\n` +
        `ATURAN KETAT WAJIB:\n` +
        `1. Jawab HANYA pertanyaan seputar fasilitas, sarana, prasarana, gedung, ruangan, dan penggunaan sistem kampus STMIK IKMI Cirebon.\n` +
        `2. Untuk pertanyaan yang menanyakan JUMLAH/TOTAL fasilitas, ruangan, gedung, atau jenis fasilitas, JAWAB HANYA BERUPA ANGKA SAJA tanpa kata tambahan, tanpa satuan (jangan tulis "unit", "ruangan", "item"), tanpa kata awalan (jangan tulis "Ada"), tanpa penjelasan, dan tanpa kata penutup apa pun (contoh: jika ditanya total fasilitas jawab "99", jika ditanya jumlah ruangan di kampus jawab "4", jika ditanya jumlah ruangan yang terdata jawab "4", jika ditanya ruangan tersedia jawab "4", jika ditanya jenis fasilitas jawab "7"). Aturan angka saja ini WAJIB dipatuhi untuk semua pertanyaan jumlah.\n` +
        `3. Bedakan metrik secara presisi: Jangan menyamakan total jenis fasilitas (${actualStats.fasilitasItem} jenis) dengan total unit fisik (${actualStats.fasilitasUnit} unit).\n` +
        `4. Jika pengguna meminta rincian atau penjelasan (misalnya "Jelaskan fasilitas yang tersedia" atau "Tolong jelaskan daftar ruangan yang tersedia"), baru berikan penjelasan atau rincian daftar secara lengkap.\n` +
        `5. Jika pertanyaan di luar konteks sistem fasilitas (politik, resep masak, film, artis, cuaca, presiden lain, pertanyaan umum), jawab PERSIS: "${OUT_OF_CONTEXT_MESSAGE}".\n` +
        `6. DILARANG KERAS membuat, menuliskan, atau mengeksekusi kode program. Jika diminta kode, tolak dengan kalimat PERSIS: "${CODE_REFUSAL_MESSAGE}".\n` +
        `7. DILARANG mengarang angka atau data inventaris di luar data aktual database di atas.\n` +
        `8. Jika data tidak tersedia atau query gagal, sampaikan secara singkat bahwa data belum dapat diambil.`;

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 12000);

    // Bangun riwayat percakapan untuk multi-turn (maks 6 entri terakhir = 3 pasang)
    const safeHistory = (Array.isArray(convHistory) ? convHistory : []).slice(-6);

    try {
        const isGemini = AI_PROVIDER === 'gemini' || (!AI_PROVIDER && !AI_API_KEY.startsWith('nvapi-') && !AI_API_KEY.startsWith('sk-'));

        // Handler untuk OpenAI-compatible (DeepSeek, OpenRouter, OpenAI, Claude, NVIDIA)
        if (!isGemini) {
            let url = 'https://api.openai.com/v1/chat/completions';
            let model = AI_MODEL || 'gpt-4o-mini';
            const headers = {
                'Authorization': `Bearer ${AI_API_KEY}`,
                'Content-Type': 'application/json'
            };

            const provLower = (AI_PROVIDER || '').toLowerCase();
            const modelLower = (AI_MODEL || '').toLowerCase();

            if (provLower === 'deepseek' || modelLower.includes('deepseek')) {
                url = 'https://api.deepseek.com/chat/completions';
                model = AI_MODEL || 'deepseek-chat';
            } else if (provLower === 'openrouter' || AI_API_KEY.startsWith('sk-or-') || modelLower.includes('/')) {
                url = 'https://openrouter.ai/api/v1/chat/completions';
                model = AI_MODEL || 'anthropic/claude-3.5-sonnet';
                headers['HTTP-Referer'] = 'http://localhost:3001';
                headers['X-Title'] = 'STMIK IKMI Campus Facilities';
            } else if (provLower === 'nvidia' || AI_API_KEY.startsWith('nvapi-')) {
                url = 'https://integrate.api.nvidia.com/v1/chat/completions';
                model = (AI_MODEL && !AI_MODEL.includes('gemini')) ? AI_MODEL : 'meta/llama-3.1-8b-instruct';
            } else if (provLower === 'openai') {
                url = 'https://api.openai.com/v1/chat/completions';
                model = AI_MODEL || 'gpt-4o-mini';
            }

            // Susun pesan dengan riwayat untuk OpenAI-compatible format
            const historyMessages = safeHistory.map(h => ({
                role: h.role === 'model' ? 'assistant' : 'user',
                content: h.text.substring(0, 300)
            }));

            const response = await fetch(url, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify({
                    model: model,
                    messages: [
                        { role: 'system', content: systemInstruction },
                        ...historyMessages,
                        { role: 'user', content: userMessage.substring(0, 500) }
                    ],
                    temperature: 0.2,
                    max_tokens: 450
                }),
                signal: controller.signal
            });

            clearTimeout(timeout);
            if (!response.ok) return null;
            const data = await response.json();
            return data?.choices?.[0]?.message?.content || null;
        }

        // Susun contents multi-turn untuk Gemini API
        const geminiContents = [];
        for (const h of safeHistory) {
            geminiContents.push({
                role: h.role, // 'user' atau 'model'
                parts: [{ text: h.text.substring(0, 400) }]
            });
        }
        // Tambahkan pesan saat ini
        geminiContents.push({
            role: 'user',
            parts: [{ text: userMessage.substring(0, 500) }]
        });

        // Panggilan Gemini API dengan fallback model otomatis jika 404
        const geminiModels = [AI_MODEL, 'gemini-1.5-flash-latest', 'gemini-2.0-flash', 'gemini-1.5-flash-001', 'gemini-1.5-pro'];
        for (const gModel of geminiModels) {
            const url = `https://generativelanguage.googleapis.com/v1beta/models/${gModel}:generateContent?key=${AI_API_KEY}`;
            const response = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    system_instruction: { parts: [{ text: systemInstruction }] },
                    contents: geminiContents,
                    generationConfig: { maxOutputTokens: 500, temperature: 0.2 }
                }),
                signal: controller.signal
            });

            if (response.ok) {
                clearTimeout(timeout);
                const data = await response.json();
                return data?.candidates?.[0]?.content?.parts?.[0]?.text || null;
            }
        }

        clearTimeout(timeout);
        return null;
    } catch (err) {
        clearTimeout(timeout);
        return null;
    }
}

/**
 * Endpoint Hot-Reload Konfigurasi AI: POST /api/config/update
 * Menerima pembaruan provider, apiKey, dan model secara real-time dari PHP Admin UI
 */
app.post('/api/config/update', (req, res) => {
    try {
        const { provider, apiKey, model } = req.body || {};
        if (provider !== undefined) {
            AI_PROVIDER = String(provider).trim().toLowerCase();
            process.env.AI_PROVIDER = AI_PROVIDER;
        }
        if (apiKey !== undefined) {
            AI_API_KEY = String(apiKey).trim();
            process.env.AI_API_KEY = AI_API_KEY;
        }
        if (model !== undefined) {
            AI_MODEL = String(model).trim();
            process.env.AI_MODEL = AI_MODEL;
        }
        console.log(`[AI CONFIG HOT-RELOAD] Provider: ${AI_PROVIDER}, Model: ${AI_MODEL}, Key: ${AI_API_KEY ? AI_API_KEY.substring(0, 8) + '...' : '(none)'}`);
        return res.json({
            status: 'ok',
            message: 'Konfigurasi AI Assistant berhasil diperbarui di memori runtime.',
            config: {
                provider: AI_PROVIDER,
                model: AI_MODEL,
                hasKey: Boolean(AI_API_KEY)
            }
        });
    } catch (err) {
        return res.status(500).json({ status: 'error', message: err.message });
    }
});

/**
 * Health Check Endpoint
 */
app.get('/api/health', async (req, res) => {
    const stats = await getActualStats();
    res.json({
        status: 'ok',
        service: 'STMIK IKMI Kampus Chatbot Service',
        port: PORT,
        timestamp: new Date().toISOString(),
        database: stats.connected ? 'connected' : 'disconnected',
        stats: stats.connected ? {
            gedung: stats.gedung,
            ruangan: stats.ruanganTotal,
            fasilitas_item: stats.fasilitasItem,
            fasilitas_unit: stats.fasilitasUnit,
            users: stats.usersTotal
        } : null
    });
});

/**
 * Endpoint Utama Chatbot: POST /api/chat
 *
 * Body JSON yang diterima:
 *   message       (string, wajib)  - pesan pengguna
 *   chatSessionId (string, opsional) - ID sesi percakapan untuk riwayat multi-turn
 */
app.post('/api/chat', async (req, res) => {
    try {
        const { message, chatSessionId } = req.body || {};

        // Validasi input kosong / tipe
        if (!message || typeof message !== 'string' || message.trim() === '') {
            return res.status(400).json({
                status: 'error',
                message: 'Pesan tidak boleh kosong.'
            });
        }

        const cleanMessage = message.trim().substring(0, 500);

        // Tahap 1: Resolusi Sesi Server-Side Pengguna (Tanpa mempercayai klaim frontend)
        const user = await resolveUserSession(req);

        // Tahap 1b: Ambil atau buat sesi percakapan (riwayat multi-turn)
        const convSession = getOrCreateConvSession(chatSessionId);

        // Tahap 2: Evaluasi Local Matcher (Hemat Token, Zero-Hallucination, RBAC, + Follow-Up)
        const localResult = await matchLocalIntent(cleanMessage, user, convSession);
        if (localResult.matched) {
            // Simpan ke riwayat percakapan (kecuali respons dari security policy — tidak perlu diingat)
            if (convSession && localResult.source !== 'security_policy') {
                pushToHistory(convSession, cleanMessage, localResult.reply);
            }
            return res.json({
                status: 'success',
                question: cleanMessage,
                response: localResult.reply,
                intent: localResult.intent,
                source: localResult.source,
                user: user ? { role: user.role_display, nama: user.nama_lengkap } : null,
                timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            });
        }

        // Tahap 3: Cek apakah pertanyaan sepenuhnya di luar konteks
        if (isOutOfContextRequest(cleanMessage)) {
            return res.json({
                status: 'success',
                question: cleanMessage,
                response: OUT_OF_CONTEXT_MESSAGE,
                intent: 'OUT_OF_CONTEXT_BLOCKED',
                source: 'security_policy',
                timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            });
        }

        // Tahap 4: Ambil data statistik database untuk konteks AI
        const stats = await getActualStats();
        if (!stats.connected) {
            return res.json({
                status: 'success',
                question: cleanMessage,
                response: DB_UNAVAILABLE_MESSAGE,
                intent: 'DB_CONNECTION_ERROR',
                source: 'system_error',
                timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            });
        }

        // Tahap 5: Panggilan Provider AI dengan riwayat percakapan & Post-Check
        const historyForAI = convSession ? convSession.history : [];
        const aiAnswer = await callAIProvider(cleanMessage, stats, user, historyForAI);
        if (aiAnswer) {
            let sanitizedAnswer = sanitizeResponse(aiAnswer);
            // Post-check: Pertanyaan yang hanya menanyakan jumlah wajib berupa angka saja
            const isCountOnly = /\b(berapa|jumlah|total|ada berapa|hitung|seberapa banyak)\b/i.test(cleanMessage) &&
                !/\b(daftar|list|jelaskan|sebutkan|rincian|rincikan|detail|tampilkan|apa saja|mana saja|bagaimana)\b/i.test(cleanMessage);
            if (isCountOnly && sanitizedAnswer !== CODE_REFUSAL_MESSAGE && sanitizedAnswer !== OUT_OF_CONTEXT_MESSAGE) {
                const numMatch = sanitizedAnswer.match(/\b\d+\b/);
                if (numMatch) {
                    sanitizedAnswer = numMatch[0];
                }
            }
            // Simpan ke riwayat percakapan
            if (convSession) {
                pushToHistory(convSession, cleanMessage, sanitizedAnswer);
            }
            return res.json({
                status: 'success',
                question: cleanMessage,
                response: sanitizedAnswer,
                intent: sanitizedAnswer === CODE_REFUSAL_MESSAGE ? 'CODING_LEAK_PREVENTED' : 'AI_GENERATED',
                source: AI_API_KEY.startsWith('nvapi-') ? 'nvidia_nim_api' : 'gemini_api',
                user: user ? { role: user.role_display, nama: user.nama_lengkap } : null,
                timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
            });
        }

        // Tahap 6: Fallback Default Menolak Sopan untuk Pertanyaan di Luar Topik
        return res.json({
            status: 'success',
            question: cleanMessage,
            response: OUT_OF_CONTEXT_MESSAGE,
            intent: 'GENERAL_OUT_OF_CONTEXT',
            source: 'security_policy',
            timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
        });

    } catch (err) {
        console.error('[Error in /api/chat]', err);
        return res.status(500).json({
            status: 'error',
            message: 'Terjadi kesalahan internal pada layanan chatbot. Silakan coba kembali.'
        });
    }
});

// Start Server
app.listen(PORT, () => {
    console.log(`=======================================================`);
    console.log(`🤖 Chatbot Service STMIK IKMI Cirebon Aktif!`);
    console.log(`📡 URL: http://localhost:${PORT}`);
    console.log(`🔗 Endpoint POST: http://localhost:${PORT}/api/chat`);
    console.log(`🩺 Healthcheck: http://localhost:${PORT}/api/health`);
    console.log(`=======================================================`);
});
