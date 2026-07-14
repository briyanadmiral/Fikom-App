@extends('layouts.app')

@section('title', 'Manajemen Peran')

@section('content_header')
    <div class="roles-header-box">
        <div class="roles-header-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <div>
            <div class="roles-header-title">Manajemen Peran</div>
            <div class="roles-header-desc">Kelola daftar role pengguna dan cakupan akses aplikasi Surat SIEGA.</div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        .roles-header-box {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem 1.3rem 1.8rem;
            border-radius: 1.1rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.3rem;
        }

        .roles-header-icon {
            background: linear-gradient(135deg, #5b65d8 0%, #8b5cf6 100%);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px rgba(91, 101, 216, .22);
            color: #fff;
            font-size: 1.7rem;
            flex: 0 0 54px;
        }

        .roles-header-title {
            font-weight: 700;
            color: #343a40;
            font-size: 1.85rem;
            margin-bottom: .13rem;
            letter-spacing: -1px;
        }

        .roles-header-desc {
            color: #636e7b;
            font-size: 1.03rem;
        }

        .role-stat-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #edf2f7;
            padding: 1.25rem;
            box-shadow: 0 4px 18px rgba(15, 23, 42, .04);
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .role-stat-card::after {
            content: '';
            position: absolute;
            width: 92px;
            height: 92px;
            right: -32px;
            bottom: -38px;
            border-radius: 50%;
            background: rgba(91, 101, 216, .08);
        }

        .role-stat-label {
            color: #6c757d;
            font-size: .78rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            margin-bottom: .35rem;
        }

        .role-stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #2d3748;
            line-height: 1;
        }

        .roles-card {
            border: none;
            border-radius: 14px;
            box-shadow: 0 4px 25px rgba(15, 23, 42, .06);
            overflow: hidden;
        }

        .roles-card .card-header {
            background: #fff;
            border-bottom: 1px solid #edf2f7;
            padding: 1.25rem 1.5rem;
        }

        .roles-card .card-title {
            font-weight: 700;
            color: #343a40;
            margin: 0;
        }

        .table-roles {
            margin-bottom: 0 !important;
        }

        .table-roles thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e9eef5 !important;
            color: #64748b;
            font-size: .76rem;
            font-weight: 800;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 1rem 1.25rem;
        }

        .table-roles tbody td {
            vertical-align: middle;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
        }

        .table-roles tbody tr:hover {
            background: #fbfdff;
        }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .45rem .75rem;
            border-radius: 999px;
            background: #eef2ff;
            color: #4f46e5;
            font-weight: 800;
            font-size: .82rem;
            letter-spacing: .02em;
        }

        .role-count-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.3rem;
            padding: .35rem .7rem;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 800;
        }

        .role-status-active {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .72rem;
            border-radius: 999px;
            background: #e8f7ef;
            color: #15803d;
            font-size: .78rem;
            font-weight: 800;
        }

        .role-status-inactive {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .4rem .72rem;
            border-radius: 999px;
            background: #f1f5f9;
            color: #64748b;
            font-size: .78rem;
            font-weight: 800;
        }

        .btn-role-action {
            width: 34px;
            height: 34px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            border: none;
            transition: all .18s ease;
        }

        .btn-role-action:hover {
            transform: translateY(-1px);
        }

        .btn-role-edit {
            background: #fff7d6;
            color: #946200;
        }

        .btn-role-delete {
            background: #ffe5e9;
            color: #b4233a;
        }

        @media (max-width: 767.98px) {
            .roles-header-box {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.2rem 1rem;
                gap: .7rem;
            }

            .roles-header-title {
                font-size: 1.25rem;
            }

            .roles-header-desc {
                font-size: .95rem;
            }
        }
    </style>
@endpush

@section('content')
    @php
        $totalRoles = $roles->count();
        $activeRoles = $roles->where('is_active', true)->count();
        $totalUsers = $roles->sum('users_count');
    @endphp

    <div class="row mb-4">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="role-stat-card">
                <div class="role-stat-label">Total Peran</div>
                <div class="role-stat-value">{{ $totalRoles }}</div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="role-stat-card">
                <div class="role-stat-label">Peran Aktif</div>
                <div class="role-stat-value">{{ $activeRoles }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="role-stat-card">
                <div class="role-stat-label">Pengguna Terhubung</div>
                <div class="role-stat-value">{{ $totalUsers }}</div>
            </div>
        </div>
    </div>

    <div class="card roles-card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h3 class="card-title mb-2 mb-sm-0">
                <i class="fas fa-list-ul mr-2 text-primary"></i>Daftar Peran Sistem
            </h3>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#roleCreateModal">
                <i class="fas fa-plus mr-1"></i>Tambah Peran
            </button>
        </div>
        <div class="card-body p-0">
            @if($roles->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="fas fa-user-tag fa-3x mb-3 text-secondary" style="opacity: 0.5;"></i>
                    <h5>Belum Ada Data</h5>
                    <p class="mb-0 small">Belum ada data peran.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table id="rolesTable" class="table table-hover table-roles w-100">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>Nama Peran</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Pengguna</th>
                                <th>Status</th>
                                <th style="width: 110px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($roles as $role)
                                <tr>
                                    <td class="font-weight-bold text-muted">#{{ $role->id }}</td>
                                    <td>
                                        <span class="role-badge">
                                            <i class="fas fa-shield-alt"></i>{{ $role->nama }}
                                        </span>
                                    </td>
                                    <td>{{ $role->deskripsi ?: 'Tidak ada deskripsi.' }}</td>
                                    <td class="text-center">
                                        <span class="role-count-pill">{{ $role->users_count }}</span>
                                    </td>
                                    <td>
                                        @if ($role->is_active ?? true)
                                            <span class="role-status-active"><i class="fas fa-circle"></i>Aktif</span>
                                        @else
                                            <span class="role-status-inactive"><i class="fas fa-circle"></i>Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn-role-action btn-role-edit js-role-edit"
                                                data-id="{{ $role->id }}"
                                                data-nama="{{ $role->nama }}"
                                                data-deskripsi="{{ $role->deskripsi }}"
                                                title="Edit peran">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button"
                                                class="btn-role-action btn-role-delete js-role-delete"
                                                data-id="{{ $role->id }}"
                                                data-nama="{{ $role->nama }}"
                                                data-users="{{ $role->users_count }}"
                                                title="Hapus peran">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div class="modal fade" id="roleCreateModal" tabindex="-1" role="dialog" aria-labelledby="roleCreateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content js-role-form" method="POST" action="{{ route('roles.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="roleCreateModalLabel">Tambah Peran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="create_nama">Nama Peran</label>
                        <input type="text" class="form-control" id="create_nama" name="nama" maxlength="255" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="create_deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="create_deskripsi" name="deskripsi" rows="3" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="roleEditModal" tabindex="-1" role="dialog" aria-labelledby="roleEditModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form class="modal-content js-role-form" id="roleEditForm" method="POST" action="#">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="roleEditModalLabel">Edit Peran</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="edit_nama">Nama Peran</label>
                        <input type="text" class="form-control" id="edit_nama" name="nama" maxlength="255" required>
                    </div>
                    <div class="form-group mb-0">
                        <label for="edit_deskripsi">Deskripsi</label>
                        <textarea class="form-control" id="edit_deskripsi" name="deskripsi" rows="3" maxlength="255"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
    <script>
        $(function () {
            $('#rolesTable').DataTable({
                responsive: true,
                autoWidth: false,
                pageLength: 10,
                language: {
                    search: 'Cari:',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ peran',
                    infoEmpty: 'Tidak ada data',
                    zeroRecords: 'Data peran tidak ditemukan',
                    paginate: {
                        first: 'Pertama',
                        last: 'Terakhir',
                        next: 'Berikutnya',
                        previous: 'Sebelumnya'
                    }
                }
            });

            $('.js-role-edit').on('click', function () {
                const id = $(this).data('id');
                $('#edit_nama').val($(this).data('nama'));
                $('#edit_deskripsi').val($(this).data('deskripsi'));
                $('#roleEditForm').attr('action', `{{ url('/roles') }}/${id}`);
                $('#roleEditModal').modal('show');
            });

            $('.js-role-delete').on('click', function () {
                const id = $(this).data('id');
                const nama = $(this).data('nama');
                const users = Number($(this).data('users'));

                if (users > 0) {
                    alert(`Peran ${nama} masih digunakan oleh ${users} pengguna dan tidak dapat dihapus.`);
                    return;
                }

                if (!confirm(`Hapus peran ${nama}?`)) {
                    return;
                }

                $.ajax({
                    url: `{{ url('/roles') }}/${id}`,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function () {
                        window.location.reload();
                    },
                    error: function (xhr) {
                        alert(xhr.responseJSON?.message || 'Gagal menghapus peran.');
                    }
                });
            });

            $('.js-role-form').on('submit', function (event) {
                event.preventDefault();
                const form = $(this);
                const button = form.find('button[type="submit"]');
                button.prop('disabled', true).text('Menyimpan...');

                $.ajax({
                    url: form.attr('action'),
                    method: form.find('input[name="_method"]').val() || form.attr('method'),
                    data: form.serialize(),
                    success: function () {
                        window.location.reload();
                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON?.errors;
                        const firstError = errors ? Object.values(errors)[0][0] : (xhr.responseJSON?.message || 'Gagal menyimpan peran.');
                        alert(firstError);
                    },
                    complete: function () {
                        button.prop('disabled', false).text(form.attr('id') === 'roleEditForm' ? 'Simpan Perubahan' : 'Simpan');
                    }
                });
            });
        });
    </script>
@endpush
