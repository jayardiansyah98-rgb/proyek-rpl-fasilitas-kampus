<div class="container-fluid p-0">
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill small mb-2">
                        <i class="bi bi-shield-lock-fill me-1"></i> Khusus Administrator
                    </span>
                    <h4 class="fw-bold mb-1">Manajemen Pengguna</h4>
                    <p class="text-muted small mb-0">Daftar akun civitas akademika dan staf yang memiliki akses ke sistem inventaris fasilitas.</p>
                </div>
                <div>
                    <button class="btn btn-primary rounded-pill px-4 shadow-sm small" disabled>
                        <i class="bi bi-plus-lg me-1"></i> Tambah Pengguna (CRUD Tahap Berikutnya)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">No. Induk (NIM/NIDN)</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Role / Peran</th>
                            <th>Jenis Civitas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users_list as $u): ?>
                        <tr>
                            <td class="ps-4 fw-bold font-monospace"><?= htmlspecialchars($u['nomor_induk']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($u['nama_lengkap']) ?></div>
                                <small class="text-muted"><?= htmlspecialchars($u['telepon'] ?? '-') ?></small>
                            </td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php if ($u['nama_role'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php elseif ($u['nama_role'] === 'petugas_sarpras'): ?>
                                    <span class="badge bg-warning text-dark">Petugas</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Pengguna</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary"><?= ucfirst($u['jenis_pengguna']) ?></span></td>
                            <td>
                                <?php if ($u['status'] === 'aktif'): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Nonaktif</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
