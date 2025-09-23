@push('styles')
<style>
    /* Kustomisasi Header Modal */
    .modal-header-custom {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        padding: 1rem 1.5rem;
    }
    .modal-header-custom .modal-title {
        font-weight: 600;
        color: #343a40;
    }
    .modal-header-custom .close {
        opacity: 1;
        text-shadow: none;
        color: #6c757d;
    }
    .form-role-container {
        background-color: #f8f9fa;
        padding: 1.5rem;
        border: 1px dashed #ced4da;
        border-radius: .5rem;
    }
    .table-roles thead th {
        background-color: #e9ecef;
        font-size: .85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .table-roles td {
        vertical-align: middle;
    }
    /* Menggunakan kembali style tombol aksi dari halaman index */
    .btn-action-role {
        width: 34px; height: 34px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; transition: .2s;
        box-shadow: 0 1px 3px rgba(0,0,0,.1);
    }
    .btn-action-role:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,.15);
    }
</style>
@endpush

{{-- ====================================================================== --}}
{{-- == SATU MODAL UNTUK MENGGANTIKAN TIGA MODAL SEBELUMNYA == --}}
{{-- ====================================================================== --}}
<div class="modal fade" id="modal-peran" tabindex="-1" role="dialog" aria-labelledby="modalPeranLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content" style="border-radius: .8rem; border:none; box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title" id="modalPeranLabel"><i class="fas fa-user-tag mr-2"></i>Manajemen Peran Pengguna</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4">

                {{-- FORM UNTUK TAMBAH & EDIT (TERPADU) --}}
                <div class="form-role-container mb-4">
                    <h6 class="font-weight-bold mb-3" id="role-form-title">
                        <i class="fas fa-plus-circle text-success mr-2"></i>Tambah Peran Baru
                    </h6>
                    <form id="form-role">
                        <input type="hidden" name="id" id="role_id">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="role_nama">Nama Peran <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" name="nama" id="role_nama" required>
                            </div>
                            <div class="form-group col-md-8">
                                <label for="role_deskripsi">Deskripsi</label>
                                <input type="text" class="form-control form-control-sm" name="deskripsi" id="role_deskripsi">
                            </div>
                        </div>
                        <div class="text-right">
                             <button type="button" class="btn btn-sm btn-secondary" id="btn-cancel-edit" style="display: none;">
                                <i class="fas fa-times mr-1"></i>Batal Edit
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary" id="btn-submit-role">
                                <i class="fas fa-save mr-1"></i><span id="btn-submit-text">Simpan</span>
                            </button>
                        </div>
                    </form>
                </div>

                {{-- TABEL DAFTAR PERAN --}}
                <h6 class="font-weight-bold mt-4 mb-2">Daftar Peran Saat Ini</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-sm table-roles" id="table-roles-modal">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Peran</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $r)
                            {{-- Menyimpan data peran di `data-role` untuk kemudahan akses via JS --}}
                            <tr id="role-row-{{ $r->id }}" data-role="{{ json_encode($r) }}">
                                <td>{{ $loop->iteration }}</td>
                                <td class="role-nama">{{ $r->nama }}</td>
                                <td class="role-deskripsi">{{ $r->deskripsi ?? '—' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-action-role btn-edit-role" data-toggle="tooltip" title="Edit">
                                        <i class="fas fa-pencil-alt fa-xs"></i>
                                    </button>
                                    <button class="btn btn-danger btn-action-role btn-delete-role" data-id="{{ $r->id }}" data-toggle="tooltip" title="Hapus">
                                        <i class="fas fa-trash-alt fa-xs"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="no-role-row">
                                <td colspan="4" class="text-center text-muted py-3">Belum ada data peran.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(function(){
    // Inisialisasi tooltip di dalam modal
    $('body').tooltip({
        selector: '[data-toggle="tooltip"]'
    });

    const storeUrl = "{{ route('roles.store') }}";
    const updateUrl = "{{ route('roles.update', ['role' => ':id']) }}";
    const deleteUrl = "{{ route('roles.destroy', ['role' => ':id']) }}";
    const csrfToken = '{{ csrf_token() }}';

    // -- FUNGSI UNTUK MERESET FORM --
    function resetRoleForm() {
        $('#form-role')[0].reset();
        $('#role_id').val('');
        $('#role-form-title').html('<i class="fas fa-plus-circle text-success mr-2"></i>Tambah Peran Baru');
        $('#btn-submit-text').text('Simpan');
        $('#btn-cancel-edit').hide();
    }

    // -- 1. SUBMIT FORM (BISA UNTUK CREATE & UPDATE) --
    $('#form-role').submit(function(e) {
        e.preventDefault();
        let id = $('#role_id').val();
        let url = id ? updateUrl.replace(':id', id) : storeUrl;
        let method = id ? 'PUT' : 'POST';
        let data = $(this).serialize();

        $.ajax({
            url: url,
            type: method,
            data: data,
            headers: {'X-CSRF-TOKEN': csrfToken},
            success: function(res) {
                Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, showConfirmButton: false, timer: 1500 });

                let role = res.role;
                let newRowHtml = `
                    <td class="align-middle">${$('#table-roles-modal tbody tr').length + 1}</td>
                    <td class="align-middle role-nama">${role.nama}</td>
                    <td class="align-middle role-deskripsi">${role.deskripsi || '—'}</td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-action-role btn-edit-role" data-toggle="tooltip" title="Edit"><i class="fas fa-pencil-alt fa-xs"></i></button>
                        <button class="btn btn-danger btn-action-role btn-delete-role" data-id="${role.id}" data-toggle="tooltip" title="Hapus"><i class="fas fa-trash-alt fa-xs"></i></button>
                    </td>`;

                if (id) { // Jika ini adalah update
                    $('#role-row-' + id).html(newRowHtml).data('role', role);
                } else { // Jika ini adalah add
                    $('#no-role-row').remove(); // Hapus pesan 'belum ada data' jika ada
                    $('#table-roles-modal tbody').append(`<tr id="role-row-${role.id}" data-role='${JSON.stringify(role)}'>${newRowHtml}</tr>`);
                }
                resetRoleForm();
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.errors?.nama?.[0] || 'Terjadi kesalahan.';
                Swal.fire('Gagal!', errorMsg, 'error');
            }
        });
    });

    // -- 2. KLIK TOMBOL EDIT --
    $(document).on('click', '.btn-edit-role', function() {
        let role = $(this).closest('tr').data('role');
        $('#role_id').val(role.id);
        $('#role_nama').val(role.nama);
        $('#role_deskripsi').val(role.deskripsi);

        $('#role-form-title').html('<i class="fas fa-pencil-alt text-warning mr-2"></i>Edit Peran');
        $('#btn-submit-text').text('Update');
        $('#btn-cancel-edit').show();
        
        // Scroll ke atas modal
        $('#modal-peran .modal-body').animate({ scrollTop: 0 }, 'fast');
        $('#role_nama').focus();
    });

    // -- 3. KLIK TOMBOL BATAL EDIT --
    $('#btn-cancel-edit').click(function() {
        resetRoleForm();
    });

    // -- 4. KLIK TOMBOL HAPUS --
    $(document).on('click', '.btn-delete-role', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Anda Yakin?',
            text: 'Peran yang dihapus tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl.replace(':id', id),
                    type: 'DELETE',
                    headers: {'X-CSRF-TOKEN': csrfToken},
                    success: function(res) {
                        $('#role-row-' + id).remove();
                        Swal.fire('Terhapus!', res.message, 'success');
                    },
                    error: function() {
                        Swal.fire('Gagal!', 'Gagal menghapus peran.', 'error');
                    }
                });
            }
        });
    });

    // -- 5. RESET FORM KETIKA MODAL DITUTUP --
    $('#modal-peran').on('hidden.bs.modal', function () {
        resetRoleForm();
    });
});
</script>
@endpush
