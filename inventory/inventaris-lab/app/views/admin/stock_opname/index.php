<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="m-0">Riwayat Stock Opname</h4>
        <a href="<?= BASE_URL; ?>/stockopname/mulai" class="btn btn-primary">
            <i class="bi bi-plus-circle-fill me-2"></i>Mulai Stock Opname Baru
        </a>
    </div>
    <hr/>
    <?php Flasher::flash(); ?>
    <div class="card">
        <div class="card-body">
            <table class="table table-striped table-bordered" id="data-table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal Opname</th>
                        <th>Dilakukan Oleh</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach($data['riwayat'] as $opname): ?>
                    <tr>
                        <td><?= $no++; ?></td>
                        <td><?= date('d F Y', strtotime($opname['tanggal_opname'])); ?></td>
                        <td><?= htmlspecialchars($opname['dilakukan_oleh']); ?></td>
                        <td>
                            <span class="badge bg-info"><?= htmlspecialchars($opname['status']); ?></span>
                        </td>
                        <td>
                            <a href="<?= BASE_URL; ?>/stockopname/detail/<?= $opname['id_opname']; ?>" class="btn btn-sm btn-primary">Lihat Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>