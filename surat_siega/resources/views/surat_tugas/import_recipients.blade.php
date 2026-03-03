@extends('layouts.app')
@section('title', 'Import Penerima ST')

@push('styles')
<style>
    .custom-header-box {
        background: linear-gradient(90deg, #4389a2 0%, #5c258d 100%);
        color: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 20px rgba(44, 62, 80, .13);
        padding: 1.5rem 2rem 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        border-left: 6px solid #3498db;
        margin-top: .5rem;
    }
    .header-icon {
        width: 54px;
        height: 54px;
        background: rgba(255, 255, 255, .15);
        color: #fff;
        font-size: 2rem;
        box-shadow: 0 2px 12px 0 rgba(52, 152, 219, .13);
    }
    .header-title {
        font-size: 1.6rem;
        font-weight: 700;
        letter-spacing: 1px;
        margin-bottom: 2px;
    }
    .header-desc {
        font-size: 1.07rem;
        color: #e9f3fa;
        font-weight: 400;
        margin-left: .1rem;
    }
    .card { border-radius: 1rem; }
    @media (max-width: 575.98px) {
        .custom-header-box { padding: 1.1rem; }
        .header-icon { width: 44px; height: 44px; font-size: 1.2rem; }
        .header-title { font-size: 1.2rem; }
        .header-desc { margin-left: 0; font-size: .98rem; }
    }
</style>
@endpush

@section('content_header')
    <div class="custom-header-box mb-4">
        <div class="d-flex align-items-center">
            <div class="header-icon rounded-circle d-flex justify-content-center align-items-center mr-3">
                <i class="fas fa-file-import fa-lg"></i>
            </div>
            <div>
                <div class="header-title">Import Penerima Surat Tugas</div>
                <div class="header-desc mt-2">
                    Upload file Excel/CSV untuk <b>import massal</b> data penerima Surat Tugas.
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-8">
            {{-- Upload Card --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-cloud-upload-alt mr-2"></i>Upload File</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        Upload file Excel (.xlsx) atau CSV dengan kolom: <strong>nama_penerima</strong> (wajib), 
                        jabatan, npp, email, instansi. Max 5MB.
                    </div>

                    <form id="uploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label>Pilih File</label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input" id="importFile" name="file" 
                                       accept=".csv,.xlsx,.xls">
                                <label class="custom-file-label" for="importFile">Pilih file...</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('import.penerima.template') }}" class="btn btn-outline-success">
                                <i class="fas fa-download mr-1"></i>Download Template
                            </a>
                            <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                                <i class="fas fa-upload mr-1"></i>Upload & Preview
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Preview Card --}}
            <div class="card card-success card-outline" id="previewCard" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-eye mr-2"></i>Preview Data</h3>
                    <div class="card-tools">
                        <span class="badge badge-success" id="successCount">0</span> valid
                        <span class="badge badge-danger ml-2" id="errorCount">0</span> error
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nama Penerima</th>
                                    <th>Jabatan</th>
                                    <th>NPP</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="previewBody"></tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-success" id="confirmBtn">
                        <i class="fas fa-check mr-1"></i>Konfirmasi & Gunakan Data
                    </button>
                    <button type="button" class="btn btn-secondary" id="cancelBtn">
                        <i class="fas fa-times mr-1"></i>Batal
                    </button>
                </div>
            </div>

            {{-- Error Card --}}
            <div class="card card-danger card-outline" id="errorCard" style="display: none;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-2"></i>Error</h3>
                </div>
                <div class="card-body">
                    <ul id="errorList" class="mb-0"></ul>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Instructions --}}
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i>Panduan</h3>
                </div>
                <div class="card-body">
                    <h6 class="text-primary">Format Kolom:</h6>
                    <ul class="small">
                        <li><strong>nama_penerima</strong> - Nama lengkap (wajib)</li>
                        <li><strong>jabatan</strong> - Jabatan/pangkat</li>
                        <li><strong>npp</strong> - NPP untuk matching internal</li>
                        <li><strong>email</strong> - Email untuk matching</li>
                        <li><strong>instansi</strong> - Unit/Fakultas</li>
                    </ul>

                    <h6 class="text-primary mt-3">Matching Internal:</h6>
                    <p class="small text-muted mb-0">
                        Jika NPP/email ditemukan di database, data akan otomatis 
                        ditautkan dengan pengguna internal.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    const uploadForm = $('#uploadForm');
    const uploadBtn = $('#uploadBtn');
    const previewCard = $('#previewCard');
    const errorCard = $('#errorCard');
    const previewBody = $('#previewBody');
    const errorList = $('#errorList');

    // File input change
    $('#importFile').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Pilih file...');
        uploadBtn.prop('disabled', !fileName);
    });

    // Upload form
    uploadForm.on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        uploadBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i>Processing...');

        $.ajax({
            url: '{{ route("import.penerima.preview") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showPreview(response.rows, response.errors);
                } else {
                    showError([response.message]);
                }
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Gagal memproses file.';
                showError([msg]);
            },
            complete: function() {
                uploadBtn.prop('disabled', false).html('<i class="fas fa-upload mr-1"></i>Upload & Preview');
            }
        });
    });

    function showPreview(rows, errors) {
        previewBody.empty();
        errorList.empty();

        rows.forEach(function(row) {
            const statusBadge = row.is_internal 
                ? '<span class="badge badge-success"><i class="fas fa-user-check"></i> Internal</span>'
                : '<span class="badge badge-secondary"><i class="fas fa-user"></i> Eksternal</span>';

            previewBody.append(`
                <tr>
                    <td>${row.row_number}</td>
                    <td>${row.nama_penerima}</td>
                    <td>${row.jabatan || '-'}</td>
                    <td>${row.npp || '-'}</td>
                    <td>${statusBadge}</td>
                </tr>
            `);
        });

        if (errors.length > 0) {
            errors.forEach(function(err) {
                errorList.append(`<li class="text-danger">Baris ${err.row}: ${err.message}</li>`);
            });
            errorCard.show();
        } else {
            errorCard.hide();
        }

        $('#successCount').text(rows.length);
        $('#errorCount').text(errors.length);
        previewCard.show();
    }

    function showError(messages) {
        errorList.empty();
        messages.forEach(function(msg) {
            errorList.append(`<li class="text-danger">${msg}</li>`);
        });
        errorCard.show();
        previewCard.hide();
    }

    // Confirm
    $('#confirmBtn').on('click', function() {
        $.ajax({
            url: '{{ route("import.penerima.confirm") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}' },
            success: function(response) {
                if (response.success) {
                    // Store in localStorage for form usage
                    localStorage.setItem('imported_recipients', JSON.stringify(response.recipients));
                    
                    alert('Data berhasil diimport! Kembali ke form Surat Tugas untuk menggunakan data.');
                    window.location.href = '{{ route("surat_tugas.create") }}';
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Gagal mengkonfirmasi import.');
            }
        });
    });

    // Cancel
    $('#cancelBtn').on('click', function() {
        previewCard.hide();
        errorCard.hide();
        uploadForm[0].reset();
        $('.custom-file-label').text('Pilih file...');
        uploadBtn.prop('disabled', true);
    });
});
</script>
@endpush
