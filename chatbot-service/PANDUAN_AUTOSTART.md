# PANDUAN OTOMATISASI STARTUP CHATBOT SERVICE (WINDOWS)
**Sistem Informasi Pendataan Fasilitas Ruangan STMIK IKMI Cirebon**

Layanan Chatbot AI berbasis Node.js (`server.js`) pada port `3001` kini dapat dijalankan secara otomatis di latar belakang (*background*) setiap kali Anda login ke Windows, tanpa perlu membuka terminal/PowerShell secara manual.

---

## 1. CARA MENGAKTIFKAN STARTUP OTOMATIS (1 KLIK)

### Metode Rekomendasi (Tanpa Perlu Hak Administrator):
1. Buka folder `chatbot-service`.
2. Klik ganda file **`enable_startup.bat`**.
3. Sistem akan membuat shortcut di direktori Startup Windows Anda (`shell:startup`) yang mengarah ke `run_silent.vbs`.
4. Chatbot langsung aktif di latar belakang dan akan otomatis berjalan setiap kali laptop dinyalakan/login.

### Metode Alternatif (Windows Task Scheduler):
Jika Anda ingin mendaftarkan tugas ke Windows Task Scheduler:
1. Klik kanan pada file **`register_task_scheduler.bat`**.
2. Pilih **"Run as administrator"**.
3. Tugas bernama `STMIK_IKMI_Chatbot_Service` akan terdaftar di Task Scheduler Windows dengan pemicu *At log on*.

---

## 2. CARA MENONAKTIFKAN STARTUP OTOMATIS

1. Buka folder `chatbot-service`.
2. Klik ganda file **`disable_startup.bat`**.
3. Sistem akan menghapus shortcut startup dari folder Startup Windows, menghapus tugas Task Scheduler (jika ada), dan menghentikan proses chatbot yang sedang aktif.

---

## 3. PENGENDALIAN MANUAL & CEK STATUS

- **Mengecek Status Chatbot**: Klik ganda `status_service.bat` untuk melihat PID, port aktif, dan status koneksi database.
- **Menjalankan Manual di Latar Belakang**: Klik ganda `start_service.bat`.
- **Menghentikan Manual**: Klik ganda `stop_service.bat`.

---

## 4. KEUNGGULAN DAN FITUR KEAMANAN

1. **Senyap (Silent Background)**: Menggunakan `run_silent.vbs` sehingga tidak ada jendela konsol hitam (CMD/PowerShell) yang muncul atau berkedip saat Windows booting.
2. **Pencegahan Proses Ganda**: Skrip secara otomatis mendeteksi apakah port 3001 sudah aktif dan merespons. Jika sudah aktif, proses startup dibatalkan agar tidak membebani memori.
3. **Pemberian Waktu untuk Laragon/MySQL**: Skrip memeriksa port MySQL `3306` hingga 25 detik saat login Windows untuk memastikan database siap. Jika Laragon belum dibuka, chatbot tetap berjalan dengan mode fallback (koneksi akan otomatis tersambung saat Laragon dinyalakan).
4. **Penghentian Presisi**: `stop_service.ps1` hanya menghentikan PID chatbot spesifik yang terdata pada `logs/chatbot.pid` atau port 3001. Tidak pernah mematikan proses Node.js lain milik pengguna.
5. **Pencatatan Log Lengkap**:
   - `logs/startup.log` : Rekaman jejak inisialisasi startup dan status kesiapan.
   - `logs/chatbot.log` : Output standar server Node.js.
   - `logs/chatbot.err.log` : Catatan error jika terjadi kendala runtime.
6. **Kerahasiaan API Key**: API key hanya tersimpan di file `.env` dan tidak pernah ditulis ke file startup atau dicetak ke log.
