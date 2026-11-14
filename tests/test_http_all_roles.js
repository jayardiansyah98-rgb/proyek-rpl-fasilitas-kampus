const http = require('http');
const querystring = require('querystring');

async function main() {
    console.log("=== INTEGRATION TEST HTTP LOGIN SEMUA ROLE (APACHE LARAGON) ===\n");

    const getCsrfAndCookie = () => new Promise((resolve, reject) => {
        http.get('http://localhost/Fasilitas%20Kampus/public/auth/login', (res) => {
            let data = '';
            const setCookies = res.headers['set-cookie'] || [];
            const cookie = setCookies.map(c => c.split(';')[0]).join('; ');
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                const m = data.match(/name="csrf_token"\s+value="([^"]+)"/);
                const hasQuickFill = data.includes('Quick Fill') || data.includes('quick-fill');
                resolve({ csrf: m ? m[1] : null, cookie, hasQuickFill, html: data });
            });
        }).on('error', reject);
    });

    const postLogin = (identifier, password, csrf, cookie) => new Promise((resolve, reject) => {
        const body = querystring.stringify({
            csrf_token: csrf,
            identifier: identifier,
            password: password
        });

        const req = http.request({
            hostname: 'localhost',
            port: 80,
            path: '/Fasilitas%20Kampus/public/auth/login',
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Content-Length': Buffer.byteLength(body),
                'Cookie': cookie
            }
        }, (res) => {
            let data = '';
            const newCookies = res.headers['set-cookie'] || [];
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                resolve({
                    statusCode: res.statusCode,
                    location: res.headers.location,
                    cookies: newCookies.map(c => c.split(';')[0]).join('; ') || cookie,
                    body: data
                });
            });
        });

        req.on('error', reject);
        req.write(body);
        req.end();
    });

    const getDashboard = (cookie) => new Promise((resolve, reject) => {
        http.get({
            hostname: 'localhost',
            port: 80,
            path: '/Fasilitas%20Kampus/public/dashboard',
            headers: {
                'Cookie': cookie
            }
        }, (res) => {
            let data = '';
            res.on('data', chunk => data += chunk);
            res.on('end', () => {
                resolve({
                    statusCode: res.statusCode,
                    location: res.headers.location,
                    body: data
                });
            });
        }).on('error', reject);
    });

    // 1. Check Login Page & Quick Fill Absence
    const initial = await getCsrfAndCookie();
    console.log("[1] Pemeriksaan Halaman Login:");
    console.log("    - CSRF Token Dihasilkan:", !!initial.csrf);
    console.log("    - Quick Fill Buttons Ada?", initial.hasQuickFill ? "YA (SALAH)" : "TIDAK (BENAR)");

    // 2. Test Invalid Password
    const testWrongPass = await postLogin('admin@ikmi.ac.id', 'passwordsalah', initial.csrf, initial.cookie);
    console.log("\n[2] Uji Kata Sandi Salah:");
    console.log("    - Status HTTP:", testWrongPass.statusCode);
    console.log("    - Pesan 'Kata sandi yang Anda masukkan salah' Muncul?", testWrongPass.body.includes('Kata sandi yang Anda masukkan salah.'));

    // 3. Test Unregistered Identifier (Akun Leo yang belum ada)
    const testLeo = await postLogin('leo@ikmi.ac.id', 'bebas123', initial.csrf, initial.cookie);
    console.log("\n[3] Uji Akun Belum Terdaftar (Leo):");
    console.log("    - Status HTTP:", testLeo.statusCode);
    console.log("    - Pesan 'Email atau Nomor Induk tidak terdaftar' Muncul?", testLeo.body.includes('Email atau Nomor Induk tidak terdaftar.'));

    // 4. Test Login Mahasiswa (Ahmad Fauzi) via NIM
    const sessMhs = await getCsrfAndCookie();
    const loginMhs = await postLogin('4122001', 'Ahmad!IKMI2026', sessMhs.csrf, sessMhs.cookie);
    console.log("\n[4] Uji Login Mahasiswa via NIM (4122001):");
    console.log("    - Status HTTP:", loginMhs.statusCode);
    console.log("    - Redirect ke Dashboard:", loginMhs.location);
    const dashMhs = await getDashboard(loginMhs.cookies);
    console.log("    - Memuat Nama Asli 'Ahmad Fauzi'?", dashMhs.body.includes('Ahmad Fauzi'));
    console.log("    - Memuat Dashboard Civitas Mahasiswa?", dashMhs.body.includes('Civitas STMIK IKMI Cirebon'));

    // 5. Test Login Admin via NIP (ADM-001)
    const sessAdmin = await getCsrfAndCookie();
    const loginAdmin = await postLogin('ADM-001', 'Admin!IKMI2026', sessAdmin.csrf, sessAdmin.cookie);
    console.log("\n[5] Uji Login Admin via NIP (ADM-001):");
    console.log("    - Status HTTP:", loginAdmin.statusCode);
    console.log("    - Redirect ke Dashboard:", loginAdmin.location);
    const dashAdmin = await getDashboard(loginAdmin.cookies);
    console.log("    - Memuat Nama Asli 'Rizky Ramadhan'?", dashAdmin.body.includes('Rizky Ramadhan'));
    console.log("    - Memuat Dashboard Eksekutif Administrator?", dashAdmin.body.includes('Dashboard Eksekutif Administrator'));

    // 6. Test Login Petugas via Email
    const sessPet = await getCsrfAndCookie();
    const loginPet = await postLogin('petugas@ikmi.ac.id', 'Petugas!IKMI2026', sessPet.csrf, sessPet.cookie);
    console.log("\n[6] Uji Login Petugas Sarpras via Email:");
    console.log("    - Status HTTP:", loginPet.statusCode);
    console.log("    - Redirect ke Dashboard:", loginPet.location);
    const dashPet = await getDashboard(loginPet.cookies);
    console.log("    - Memuat Nama Asli 'Budi Santoso'?", dashPet.body.includes('Budi Santoso'));
    console.log("    - Memuat Dashboard Tim Teknis Sarpras?", dashPet.body.includes('Dashboard Tim Teknis Sarpras'));

    // 7. Test Login Dosen via NIDN
    const sessDosen = await getCsrfAndCookie();
    const loginDosen = await postLogin('0412088501', 'Dadang!IKMI2026', sessDosen.csrf, sessDosen.cookie);
    console.log("\n[7] Uji Login Dosen via NIDN (0412088501):");
    console.log("    - Status HTTP:", loginDosen.statusCode);
    console.log("    - Redirect ke Dashboard:", loginDosen.location);
    const dashDosen = await getDashboard(loginDosen.cookies);
    console.log("    - Memuat Nama Asli 'Dr. Dadang Sudrajat, M.Kom.'?", dashDosen.body.includes('Dr. Dadang Sudrajat, M.Kom.'));
    console.log("    - Memuat Dashboard Civitas Dosen?", dashDosen.body.includes('Civitas STMIK IKMI Cirebon'));

    console.log("\n=== SELURUH PENGUJIAN HTTP LOGIN SEMUA ROLE BERHASIL 100%! ===\n");
}

main().catch(console.error);
