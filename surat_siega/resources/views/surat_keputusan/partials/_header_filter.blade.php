{{-- resources/views/surat_keputusan/partials/_header_filter.blade.php --}}
{{-- ✅ FASE 1.1: Advanced Search & Filter UI --}}

@php
    $currentSearch = request('search', '');
    $currentStatus = request('status', '');
    $currentTahun = request('tahun', '');
    $currentBulan = request('bulan', '');
    $currentPenandatangan = request('penandatangan', '');
    $currentPembuat = request('pembuat', '');
    $currentTanggalDari = request('tanggal_dari', '');
    $currentTanggalSampai = request('tanggal_sampai', '');
    $currentSort = request('sort', 'created_at');
    $currentOrder = request('order', 'desc');
    
    // Check if any filter is active
    $hasActiveFilter = !empty($currentSearch) || !empty($currentStatus) || !empty($currentTahun) 
        || !empty($currentBulan) || !empty($currentPenandatangan) || !empty($currentPembuat)
        || !empty($currentTanggalDari) || !empty($currentTanggalSampai);
@endphp

<div class="card card-outline card-primary mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search"></i> Pencarian & Filter
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    
    <div class="card-body">
        <form method="GET" action="{{ route('surat_keputusan.index') }}" id="filterForm">
            <div class="row">
                {{-- Search Box --}}
                <div class="col-md-4 mb-3">
                    <label for="search" class="form-label">
                        <i class="fas fa-search"></i> Cari Kata Kunci
                    </label>
                    <input 
                        type="text" 
                        class="form-control" 
                        id="search" 
                        name="search" 
                        placeholder="Nomor, Tentang, atau Nama Pembuat..."
                        value="{{ $currentSearch }}"
                    >
                    <small class="form-text text-muted">Cari di nomor SK, perihal, atau nama pembuat</small>
                </div>

                {{-- Filter Status --}}
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">
                        <i class="fas fa-flag"></i> Status
                    </label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="disetujui" {{ $currentStatus === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ $currentStatus === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        <option value="terbit" {{ $currentStatus === 'terbit' ? 'selected' : '' }}>Terbit</option>
                        <option value="arsip" {{ $currentStatus === 'arsip' ? 'selected' : '' }}>Arsip</option>
                    </select>
                </div>

                {{-- Filter Tahun --}}
                <div class="col-md-2 mb-3">
                    <label for="tahun" class="form-label">
                        <i class="fas fa-calendar-alt"></i> Tahun
                    </label>
                    <select class="form-control" id="tahun" name="tahun">
                        <option value="">Semua Tahun</option>
                        @foreach($filterData['tahun'] ?? [] as $thn)
                            <option value="{{ $thn }}" {{ $currentTahun == $thn ? 'selected' : '' }}>
                                {{ $thn }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Bulan --}}
                <div class="col-md-2 mb-3">
                    <label for="bulan" class="form-label">
                        <i class="fas fa-calendar"></i> Bulan
                    </label>
                    <select class="form-control" id="bulan" name="bulan">
                        <option value="">Semua Bulan</option>
                        @foreach($filterData['bulan'] ?? [] as $key => $bln)
                            <option value="{{ $key }}" {{ $currentBulan == $key ? 'selected' : '' }}>
                                {{ $bln }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filter Penandatangan --}}
                <div class="col-md-2 mb-3">
                    <label for="penandatangan" class="form-label">
                        <i class="fas fa-user-tie"></i> Penandatangan
                    </label>
                    <select class="form-control" id="penandatangan" name="penandatangan">
                        <option value="">Semua Penandatangan</option>
                        @foreach($filterData['penandatangan'] ?? [] as $pejabat)
                            <option value="{{ $pejabat->id }}" {{ $currentPenandatangan == $pejabat->id ? 'selected' : '' }}>
                                {{ $pejabat->nama_lengkap }}
                                @if($pejabat->jabatan)
                                    ({{ $pejabat->jabatan }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                {{-- Filter Pembuat --}}
                <div class="col-md-3 mb-3">
                    <label for="pembuat" class="form-label">
                        <i class="fas fa-user-edit"></i> Pembuat
                    </label>
                    <select class="form-control" id="pembuat" name="pembuat">
                        <option value="">Semua Pembuat</option>
                        @foreach($filterData['pembuat'] ?? [] as $user)
                            <option value="{{ $user->id }}" {{ $currentPembuat == $user->id ? 'selected' : '' }}>
                                {{ $user->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal Range --}}
                <div class="col-md-3 mb-3">
                    <label for="tanggal_dari" class="form-label">
                        <i class="fas fa-calendar-day"></i> Tanggal Dari
                    </label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="tanggal_dari" 
                        name="tanggal_dari" 
                        value="{{ $currentTanggalDari }}"
                    >
                </div>

                <div class="col-md-3 mb-3">
                    <label for="tanggal_sampai" class="form-label">
                        <i class="fas fa-calendar-check"></i> Tanggal Sampai
                    </label>
                    <input 
                        type="date" 
                        class="form-control" 
                        id="tanggal_sampai" 
                        name="tanggal_sampai" 
                        value="{{ $currentTanggalSampai }}"
                    >
                </div>

                {{-- Sorting --}}
                <div class="col-md-2 mb-3">
                    <label for="sort" class="form-label">
                        <i class="fas fa-sort"></i> Urutkan Berdasarkan
                    </label>
                    <select class="form-control" id="sort" name="sort">
                        <option value="created_at" {{ $currentSort === 'created_at' ? 'selected' : '' }}>Tgl Dibuat</option>
                        <option value="tanggal_surat" {{ $currentSort === 'tanggal_surat' ? 'selected' : '' }}>Tgl Surat</option>
                        <option value="nomor" {{ $currentSort === 'nomor' ? 'selected' : '' }}>Nomor</option>
                    </select>
                </div>

                <div class="col-md-1 mb-3">
                    <label for="order" class="form-label">Arah</label>
                    <select class="form-control" id="order" name="order">
                        <option value="desc" {{ $currentOrder === 'desc' ? 'selected' : '' }}>↓</option>
                        <option value="asc" {{ $currentOrder === 'asc' ? 'selected' : '' }}>↑</option>
                    </select>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Terapkan Filter
                    </button>
                    
                    <a href="{{ route('surat_keputusan.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>

                    @if($hasActiveFilter)
                        <span class="badge badge-info ml-2">
                            <i class="fas fa-info-circle"></i> Filter Aktif
                        </span>
                    @endif

                    {{-- Export Button (akan diimplementasi nanti) --}}
                    {{-- <button type="button" class="btn btn-success float-right" id="btnExportExcel" disabled>
                        <i class="fas fa-file-excel"></i> Export Excel
                    </button> --}}
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-submit on filter change (optional)
    $('#status, #tahun, #bulan, #penandatangan, #pembuat, #sort, #order').on('change', function() {
        // Uncomment untuk auto-submit
        // $('#filterForm').submit();
    });

    // Validasi tanggal range
    $('#tanggal_sampai').on('change', function() {
        const dari = $('#tanggal_dari').val();
        const sampai = $(this).val();
        
        if (dari && sampai && sampai < dari) {
            alert('Tanggal sampai tidak boleh lebih awal dari tanggal dari');
            $(this).val('');
        }
    });
});
</script>
@endpush
