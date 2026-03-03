</div> </main> </div> <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        // Inisialisasi untuk tabel biasa
        $('#data-table').DataTable();
        $('[data-bs-toggle="tooltip"]').tooltip();

        // Inisialisasi untuk tabel log
        const tableLog = $('#tabel-log').DataTable({
            "order": [[ 1, "desc" ]], // Urutkan berdasarkan kolom Waktu (indeks 1) DESC
            "columnDefs": [{
                "searchable": false,
                "orderable": false,
                "targets": 0
            }]
        });
        
        // Fungsi untuk penomoran dinamis
        tableLog.on('draw.dt', function () {
            const pageInfo = tableLog.page.info();
            tableLog.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                cell.innerHTML = i + 1 + pageInfo.start;
            });
        }).draw();

        // Event listener untuk modal detail barang
        $('.tombol-detail').on('click', function() {
            const kode = $(this).data('kode');
            const nama = $(this).data('nama');
            const jenis = $(this).data('jenis');
            const deskripsi = $(this).data('deskripsi');
            const foto = $(this).data('foto');
            
            $('#detail-kode').text(kode);
            $('#detail-nama').text(nama);
            $('#detail-jenis').text(jenis);
            $('#detail-deskripsi').text(deskripsi);

            if (foto) {
                $('#detail-foto').attr('src', '<?= BASE_URL; ?>/assets/uploads/barang/' + foto);
            } else {
                $('#detail-foto').attr('src', 'https://via.placeholder.com/400x300.png?text=No+Image');
            }
        });

        // Event listener untuk modal ubah stok
        $('.tombol-ubah-stok').on('click', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const total = $(this).data('total');
            $('#id-barang-stok').val(id);
            $('#nama-barang-stok').text(nama);
            $('#jumlah-total-lama').val(total);
        });

        // Event listener untuk modal peminjaman user
        $('.tombol-pinjam').on('click', function() {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            const tersedia = $(this).data('tersedia');
            $('#modal-id-barang').val(id);
            $('#modal-nama-barang').val(nama);
            $('#modal-jumlah-tersedia').text(tersedia);
            $('#modal-jumlah-pinjam').attr('max', tersedia);
            const tglPinjamInput = $('#modal-tgl-pinjam');
            const tglKembaliInput = $('#modal-tgl-kembali');
            const hariIni = new Date().toISOString().split('T')[0];
            tglPinjamInput.val(hariIni);
            tglPinjamInput.attr('min', hariIni);
            function setReturnDate(loanDateStr) {
                let loanDate = new Date(loanDateStr);
                loanDate.setDate(loanDate.getDate() + 7);
                let returnDateStr = loanDate.toISOString().split('T')[0];
                tglKembaliInput.attr('min', loanDateStr);
                tglKembaliInput.val(returnDateStr);
            }
            setReturnDate(hariIni);
            tglPinjamInput.on('change', function() {
                setReturnDate($(this).val());
            });
        });

        // Event listener untuk modal ubah tanggal ACC
        $('.tombol-ubah-tanggal').on('click', function() {
            const id = $(this).data('id');
            const tglLama = $(this).data('tgl');
            const tglPinjam = $(this).data('tglpinjam').split(' ')[0];
            $('#modal-id-peminjaman-tanggal').val(id);
            $('#modal-tgl-kembali-baru').attr('min', tglPinjam);
            $('#modal-tgl-kembali-baru').val(tglLama);
        });

        // Event listener untuk modal setujui
        $('.tombol-setujui').on('click', function() {
            const id = $(this).data('id');
            $('#modal-id-setujui').val(id);
        });

        // Event listener untuk modal kembali
        $('.tombol-kembali').on('click', function() {
            const id = $(this).data('id');
            $('#modal-id-kembali').val(id);
        });
    });
    
</script>
</body>
</html>