<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            Catatan Seluruh Aktivitas Sistem
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tabel-log">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Waktu</th>
                            <th>Pengguna (Email)</th>
                            <th>Aktivitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($data['logs'] as $log): ?>
                        <tr>
                            <td></td> <td data-order="<?= strtotime($log['waktu']); ?>">
                                <?= date('d M Y, H:i:s', strtotime($log['waktu'])); ?>
                            </td>
                            
                            <td><?= htmlspecialchars($log['email_user']); ?></td>
                            <td><?= htmlspecialchars($log['aktivitas']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>