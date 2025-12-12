{{-- resources/views/shared/_header_filter.blade.php --}}
{{-- Unified filter component for both Surat Tugas and Surat Keputusan --}}

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

    $hasActiveFilter =
        !empty($currentSearch) ||
        !empty($currentStatus) ||
        !empty($currentTahun) ||
        !empty($currentBulan) ||
        !empty($currentPenandatangan) ||
        !empty($currentPembuat) ||
        !empty($currentTanggalDari) ||
        !empty($currentTanggalSampai);

    // Default values - can be overridden by parent view
    $formAction = $formAction ?? url()->current();
    $searchPlaceholder = $searchPlaceholder ?? 'Nomor, Perihal, atau Nama...';
    $searchHint = $searchHint ?? 'Cari di nomor surat, perihal, atau nama pembuat';
    $pembuatLabel = $pembuatLabel ?? 'Pembuat';
    $showStatusTerbit = $showStatusTerbit ?? false; // Only for SK
@endphp

<div class="card card-outline card-primary filter-card mb-3">
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
        <form method="GET" action="{{ $formAction }}" id="filterForm">
            <div class="row">
                {{-- Search --}}
                <div class="col-md-4 mb-3">
                    <label for="search" class="form-label">
                        <i class="fas fa-search mr-1"></i> Cari Kata Kunci
                    </label>
                    <input type="text" id="search" name="search" class="form-control"
                        placeholder="{{ $searchPlaceholder }}" value="{{ $currentSearch }}">
                    <small class="form-text text-muted">
                        {{ $searchHint }}
                    </small>
                </div>

                {{-- Status --}}
                <div class="col-md-2 mb-3">
                    <label for="status" class="form-label">
                        <i class="fas fa-flag mr-1"></i> Status
                    </label>
                    <select id="status" name="status" class="form-control">
                        <option value="">Semua Status</option>
                        <option value="draft" {{ $currentStatus === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending" {{ $currentStatus === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="disetujui" {{ $currentStatus === 'disetujui' ? 'selected' : '' }}>Disetujui</option>
                        <option value="ditolak" {{ $currentStatus === 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                        @if($showStatusTerbit)
                            <option value="terbit" {{ $currentStatus === 'terbit' ? 'selected' : '' }}>Terbit</option>
                            <option value="arsip" {{ $currentStatus === 'arsip' ? 'selected' : '' }}>Arsip</option>
                        @endif
                    </select>
                </div>

                {{-- Tahun --}}
                <div class="col-md-2 mb-3">
                    <label for="tahun" class="form-label">
                        <i class="fas fa-calendar-alt mr-1"></i> Tahun
                    </label>
                    <select id="tahun" name="tahun" class="form-control">
                        <option value="">Semua Tahun</option>
                        @foreach ($filterData['tahun'] ?? [] as $thn)
                            <option value="{{ $thn }}"
                                {{ (string) $currentTahun === (string) $thn ? 'selected' : '' }}>
                                {{ $thn }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Bulan --}}
                <div class="col-md-2 mb-3">
                    <label for="bulan" class="form-label">
                        <i class="fas fa-calendar mr-1"></i> Bulan
                    </label>
                    <select id="bulan" name="bulan" class="form-control">
                        <option value="">Semua Bulan</option>
                        @foreach ($filterData['bulan'] ?? [] as $key => $bln)
                            <option value="{{ $key }}"
                                {{ (string) $currentBulan === (string) $key ? 'selected' : '' }}>
                                {{ $bln }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Penandatangan --}}
                <div class="col-md-2 mb-3">
                    <label for="penandatangan" class="form-label">
                        <i class="fas fa-user-tie mr-1"></i> Penandatangan
                    </label>
                    <select id="penandatangan" name="penandatangan" class="form-control">
                        <option value="">Semua Penandatangan</option>
                        @foreach ($filterData['penandatangan'] ?? [] as $pejabat)
                            <option value="{{ $pejabat->id }}"
                                {{ (string) $currentPenandatangan === (string) $pejabat->id ? 'selected' : '' }}>
                                {{ $pejabat->nama_lengkap }}
                                @if ($pejabat->jabatan)
                                    ({{ $pejabat->jabatan }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
                {{-- Pembuat --}}
                <div class="col-md-3 mb-3">
                    <label for="pembuat" class="form-label">
                        <i class="fas fa-user-edit mr-1"></i> {{ $pembuatLabel }}
                    </label>
                    <select id="pembuat" name="pembuat" class="form-control">
                        <option value="">Semua Pembuat</option>
                        @foreach ($filterData['pembuat'] ?? [] as $user)
                            <option value="{{ $user->id }}"
                                {{ (string) $currentPembuat === (string) $user->id ? 'selected' : '' }}>
                                {{ $user->nama_lengkap }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tanggal dari --}}
                <div class="col-md-3 mb-3">
                    <label for="tanggal_dari" class="form-label">
                        <i class="fas fa-calendar-day mr-1"></i> Tanggal Dari
                    </label>
                    <input type="date" id="tanggal_dari" name="tanggal_dari" class="form-control"
                        value="{{ $currentTanggalDari }}">
                </div>

                {{-- Tanggal sampai --}}
                <div class="col-md-3 mb-3">
                    <label for="tanggal_sampai" class="form-label">
                        <i class="fas fa-calendar-check mr-1"></i> Tanggal Sampai
                    </label>
                    <input type="date" id="tanggal_sampai" name="tanggal_sampai" class="form-control"
                        value="{{ $currentTanggalSampai }}">
                </div>

                {{-- Sort --}}
                <div class="col-md-2 mb-3">
                    <label for="sort" class="form-label">
                        <i class="fas fa-sort mr-1"></i> Urutkan
                    </label>
                    <select id="sort" name="sort" class="form-control">
                        <option value="created_at" {{ $currentSort === 'created_at' ? 'selected' : '' }}>Tgl Dibuat</option>
                        <option value="tanggal_surat" {{ $currentSort === 'tanggal_surat' ? 'selected' : '' }}>Tgl Surat</option>
                        <option value="nomor" {{ $currentSort === 'nomor' ? 'selected' : '' }}>Nomor</option>
                    </select>
                </div>

                {{-- Order --}}
                <div class="col-md-1 mb-3">
                    <label for="order" class="form-label">Arah</label>
                    <select id="order" name="order" class="form-control">
                        <option value="desc" {{ $currentOrder === 'desc' ? 'selected' : '' }}>↓</option>
                        <option value="asc" {{ $currentOrder === 'asc' ? 'selected' : '' }}>↑</option>
                    </select>
                </div>
            </div>

            <div class="row mt-2">
                <div class="col-12 d-flex flex-wrap justify-content-between align-items-center">
                    <div class="mb-2">
                        <button type="submit" class="btn btn-primary mr-2">
                            <i class="fas fa-filter mr-1"></i> Terapkan Filter
                        </button>
                        <a href="{{ $formAction }}" class="btn btn-outline-secondary" id="btnResetFilter">
                            <i class="fas fa-redo mr-1"></i> Reset
                        </a>

                        @if ($hasActiveFilter)
                            <span class="badge badge-info ml-2">
                                <i class="fas fa-info-circle mr-1"></i> Filter aktif
                            </span>
                        @endif
                    </div>

                    <small class="text-muted mb-2">
                        Gunakan kombinasi filter untuk mempersempit hasil pencarian.
                    </small>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        $(function() {
            $('#tanggal_sampai').on('change', function() {
                const dari = $('#tanggal_dari').val();
                const sampai = $(this).val();

                if (dari && sampai && sampai < dari) {
                    alert('Tanggal sampai tidak boleh lebih awal dari tanggal dari.');
                    $(this).val('');
                }
            });

            $('#btnResetFilter').on('click', function(e) {
                e.preventDefault();
                window.location.href = '{{ $formAction }}';
            });
        });
    </script>
@endpush
