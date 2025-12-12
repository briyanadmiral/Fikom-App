{{-- resources/views/surat_keputusan/partials/attachments_section.blade.php --}}
{{-- ✅ FASE 1.2: Section Lampiran File --}}

@php
    $isEditable = $isEdit && in_array($keputusan->status_surat ?? 'draft', ['draft', 'ditolak']);
    $attachments = $keputusan->attachments ?? collect();
@endphp

<div class="card card-outline card-info">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-paperclip"></i> Lampiran Dokumen Pendukung
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>

    <div class="card-body">
        {{-- Upload Form (hanya tampil jika editable) --}}
        @if ($isEditable)
            <form action="{{ route('surat_keputusan.attachments.upload', $keputusan->id) }}" method="POST"
                enctype="multipart/form-data" id="formUploadAttachment">
                @csrf

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="kategori">Kategori Dokumen <span class="text-danger">*</span></label>
                            <select class="form-control @error('kategori') is-invalid @enderror" name="kategori"
                                id="kategori" required>
                                <option value="">-- Pilih Kategori --</option>
                                <option value="proposal">Proposal</option>
                                <option value="rab">RAB (Rencana Anggaran Biaya)</option>
                                <option value="surat_pengantar">Surat Pengantar</option>
                                <option value="dokumentasi">Dokumentasi</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                            @error('kategori')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="form-group">
                            <label for="file">File <span class="text-danger">*</span></label>
                            <div class="custom-file">
                                <input type="file" class="custom-file-input @error('file') is-invalid @enderror"
                                    id="file" name="file"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.zip,.rar" required>
                                <label class="custom-file-label" for="file">Pilih file...</label>
                                @error('file')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted">
                                Format: PDF, Word, Excel, Gambar, ZIP/RAR. Max: 10 MB
                            </small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="deskripsi">Deskripsi/Keterangan (Opsional)</label>
                            <textarea class="form-control @error('deskripsi') is-invalid @enderror" name="deskripsi" id="deskripsi" rows="2"
                                maxlength="500" placeholder="Keterangan singkat tentang file ini..."></textarea>
                            @error('deskripsi')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </form>

            <hr class="my-3">
        @endif

        <table class="table table-sm table-hover">
            <thead class="thead-light">
                <tr>
                    <th width="5%">
                        <i class="fas fa-file"></i>
                    </th>
                    <th width="35%">Nama File</th>
                    <th width="15%">Kategori</th>
                    <th width="10%">Ukuran</th>
                    <th width="20%">Diunggah Oleh</th>
                    <th width="15%" class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($keputusan->attachments as $attachment)
                    <tr>
                        <td class="text-center">
                            <i class="{{ $attachment->file_icon }}"></i>
                        </td>
                        <td>
                            <strong>{{ $attachment->nama_file }}</strong>
                            @if ($attachment->deskripsi)
                                <br><small class="text-muted">{{ $attachment->deskripsi }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-info">
                                {{ $attachment->kategori_label }}
                            </span>
                        </td>
                        <td>{{ $attachment->file_size_human }}</td>
                        <td>
                            <small class="text-muted">
                                {{ $attachment->uploader->nama_lengkap ?? 'System' }}<br>
                                {{ $attachment->created_at->format('d/m/Y H:i') }}
                            </small>
                        </td>
                        <td class="text-right">
                            {{-- ✅ FIXED: Tambahkan $keputusan->id di route --}}
                            <a href="{{ route('surat_keputusan.attachments.download', [$keputusan->id, $attachment->id]) }}"
                                class="btn btn-sm btn-primary" title="Download">
                                <i class="fas fa-download"></i>
                            </a>

                            @if (in_array($keputusan->status_surat, ['draft', 'ditolak']))
                                <form
                                    action="{{ route('surat_keputusan.attachments.delete', [$keputusan->id, $attachment->id]) }}"
                                    method="POST" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-sm btn-danger btn-delete" 
                                        title="Hapus" data-item-name="lampiran {{ $attachment->nama_file }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i><br>
                            Belum ada lampiran dokumen
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Info total lampiran --}}
        @if ($keputusan->attachments->count() > 0)
            <div class="text-muted small mt-2">
                <i class="fas fa-info-circle"></i>
                Total {{ $keputusan->attachments->count() }} lampiran
            </div>
        @endif

    </div>
</div>

@push('scripts')
    <script>
        $(document).ready(function() {
            // Update label filename saat file dipilih
            $('#file').on('change', function() {
                const fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').html(fileName);
            });

            // Auto-clear form after submit (optional)
            $('#formUploadAttachment').on('submit', function() {
                // Show loading indicator
                $(this).find('button[type="submit"]').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
            });
        });
    </script>
@endpush
