@extends('layouts.app')
@section('title', 'Surat Keputusan Saya')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <style>
        body {
            background: #f7faff;
        }

        /* === HEADER ATAS === */
        .surat-header {
            background: #f3f6fa;
            padding: 1.3rem 2.2rem 1.3rem 1.8rem;
            border-radius: 1.1rem;
            margin-bottom: 2.2rem;
            border: 1px solid #e0e6ed;
            display: flex;
            align-items: center;
            gap: 1.3rem;
        }

        .surat-header .icon {
            background: linear-gradient(135deg, #6f42c1 0, #9b59b6 100%);
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            box-shadow: 0 1px 10px #6f42c130;
            font-size: 2rem;
        }

        .surat-header-title {
            font-weight: bold;
            color: #5a2d91;
            font-size: 1.85rem;
            margin-bottom: .13rem;
            letter-spacing: -1px;
        }

        .surat-header-desc {
            color: #636e7b;
            font-size: 1.03rem;
        }

        /* === STATISTIK === */
        .stat-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.2rem;
            margin: 1.5rem 0 2.1rem;
            width: 100%;
            max-width: 1000px;
        }

        .stat-card {
            border-radius: .85rem;
            border: none;
            background: #fff;
        }

        .stat-card .card-body {
            text-align: center;
            padding: 1.15rem 1rem;
        }

        .stat-card .icon {
            font-size: 2.3rem;
            margin-bottom: .5rem;
        }

        .stat-card .label {
            color: #6c757d;
            font-size: .83rem;
            margin-bottom: .25rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .stat-card .value {
            font-size: 2.1rem;
            font-weight: 700;
            line-height: 1.1;
        }

        /* === CARD STYLING === */
        .card.filter-card {
            margin-bottom: 2.2rem;
            border-radius: 1rem;
        }

        .card.filter-card .card-header {
            background: #f8fafc;
            border-radius: 1rem 1rem 0 0;
            border: none;
        }

        .card.filter-card .card-body {
            padding-bottom: .7rem;
        }

        .card.data-card {
            border-radius: 1rem;
        }

        .card.data-card .card-body {
            padding-top: 1.2rem;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .table {
            background: #fff;
        }

        .badge-pill {
            padding: .45rem .85rem;
            font-size: .85rem;
            font-weight: 600;
            letter-spacing: .3px;
        }

        /* === DROPDOWN STYLING === */
        .dropdown-menu .dropdown-item {
            cursor: pointer;
            padding: 0.5rem 1rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .dropdown-menu .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }

        .dropdown-item.text-info { color: #17a2b8 !important; }
        .dropdown-item.text-danger { color: #dc3545 !important; }

        .dropdown-item.text-info:hover {
            background-color: #17a2b8 !important;
            color: #fff !important;
        }

        .dropdown-item.text-danger:hover {
            background-color: #dc3545 !important;
            color: #fff !important;
        }

        .dropdown-item:hover i {
            color: inherit !important;
        }

        /* === QUICK VIEW SPINNER === */
        .quickview-spinner {
            position: absolute;
            top: 48%;
            left: 48%;
            z-index: 10;
            display: none;
        }

        /* === RESPONSIVE === */
        @media (max-width:767.98px) {
            .surat-header {
                flex-direction: column;
                align-items: flex-start;
                padding: 1.2rem 1rem;
                gap: .7rem;
            }

            .stat-wrapper {
                grid-template-columns: minmax(0, 1fr);
                gap: .8rem;
            }

            .surat-header-title {
                font-size: 1.18rem;
            }

            .surat-header-desc {
                font-size: .99rem;
            }

            .card.filter-card,
            .card.data-card {
                border-radius: .6rem;
            }
        }
    </style>
@endpush

@section('content_header')
    @php
        // Fallback stats
        $stats = $stats ?? [
            'draft' => 0,
            'pending' => 0,
            'disetujui' => 0,
            'terbit' => 0,
            'arsip' => 0,
        ];
    @endphp

    {{-- Header Title --}}
    <div class="surat-header mt-2 mb-3">
        <span class="icon">
            <i class="fas fa-gavel text-white"></i>
        </span>
        <div>
            <div class="surat-header-title">
                Surat Keputusan Saya
            </div>
            <div class="surat-header-desc">
                Daftar Surat Keputusan yang <b>Anda buat</b>, <b>Anda tanda tangani</b>, atau <b>Anda terlibat</b> di dalamnya.
            </div>
        </div>
    </div>

    {{-- Statistik SK Saya --}}
    <div class="d-flex justify-content-center w-100 mb-3">
        <div class="stat-wrapper py-1 mx-auto">
            @foreach ([
                'draft' => ['icon' => 'fa-file-alt', 'label' => 'Draft', 'count' => $stats['draft'] ?? 0, 'color' => 'secondary'],
                'pending' => ['icon' => 'fa-hourglass-half', 'label' => 'Pending', 'count' => $stats['pending'] ?? 0, 'color' => 'warning'],
                'disetujui' => ['icon' => 'fa-check-circle', 'label' => 'Disetujui', 'count' => $stats['disetujui'] ?? 0, 'color' => 'success'],
                'terbit' => ['icon' => 'fa-share-square', 'label' => 'Terbit', 'count' => $stats['terbit'] ?? 0, 'color' => 'info'],
                'arsip' => ['icon' => 'fa-archive', 'label' => 'Arsip', 'count' => $stats['arsip'] ?? 0, 'color' => 'dark'],
            ] as $status => $info)
                <div class="stat-card card shadow-sm mx-2">
                    <div class="card-body">
                        <div class="icon text-{{ $info['color'] }}">
                            <i class="fas {{ $info['icon'] }}"></i>
                        </div>
                        <div class="label">{{ $info['label'] }}</div>
                        <div class="value text-{{ $info['color'] }}">{{ $info['count'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid px-2">

        {{-- Filter & Pencarian --}}
        <div class="card filter-card mb-4 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 font-weight-bold">
                    <i class="fas fa-filter mr-2 text-primary"></i>Filter & Pencarian
                </h5>
            </div>
            <div class="card-body">
                <form class="row">
                    <div class="col-md-6 form-group mb-2">
                        <input id="globalSearch" type="text" class="form-control"
                            placeholder="Cari berdasarkan nomor, perihal, atau penandatangan...">
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <select id="statusFilter" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="draft">Draft</option>
                            <option value="pending">Pending</option>
                            <option value="disetujui">Disetujui</option>
                            <option value="ditolak">Ditolak</option>
                            <option value="terbit">Terbit</option>
                            <option value="arsip">Arsip</option>
                        </select>
                    </div>
                    <div class="col-md-3 form-group mb-2">
                        <button id="resetFilters" class="btn btn-outline-secondary w-100" type="button">
                            <i class="fas fa-redo mr-1"></i>Reset Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabel SK Saya --}}
        <div class="card data-card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table-sk-saya" class="table table-hover" style="width:100%">
                        <thead>
                            <tr class="text-center">
                                <th>No</th>
                                <th>Nomor SK</th>
                                <th>Tentang / Perihal</th>
                                <th>Tgl Surat</th>
                                <th>Penandatangan</th>
                                <th>Disetujui</th>
                                <th>Status</th>
                                <th style="width:80px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($list as $h)
                                @php
                                    $tgl = $h->tanggal_surat ?? $h->tanggal_asli;
                                    $approved = $h->approved_at ?? null;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $h->nomor ?? '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($h->tentang, 70) }}</td>

                                    {{-- Tgl Surat --}}
                                    <td class="text-center" 
                                        data-sort="{{ $tgl instanceof \Carbon\Carbon ? $tgl->timestamp : 0 }}">
                                        {{ $tgl instanceof \Carbon\Carbon ? $tgl->format('d M Y') : '-' }}
                                    </td>

                                    <td>{{ $h->penandatanganUser?->nama_lengkap ?? '—' }}</td>

                                    {{-- Disetujui --}}
                                    <td class="text-center" 
                                        data-sort="{{ $approved instanceof \Carbon\Carbon ? $approved->timestamp : 0 }}">
                                        @if ($approved instanceof \Carbon\Carbon)
                                            {{ $approved->format('d M Y H:i') }}
                                            <br><small class="text-muted">
                                                <i class="far fa-check-circle"></i> {{ $approved->diffForHumans() }}
                                            </small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="text-center">
                                        @php
                                            $badgeMap = [
                                                'draft' => 'secondary',
                                                'pending' => 'warning',
                                                'disetujui' => 'success',
                                                'ditolak' => 'danger',
                                                'terbit' => 'info',
                                                'arsip' => 'dark',
                                            ];
                                            $badge = $badgeMap[$h->status_surat] ?? 'secondary';
                                        @endphp
                                        <span class="badge badge-pill badge-{{ $badge }}">
                                            {{ ucfirst($h->status_surat) }}
                                        </span>
                                    </td>

                                    {{-- Aksi --}}
                                    <td class="text-center">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                                data-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                {{-- Lihat Detail --}}
                                                <a class="dropdown-item text-info quick-view"
                                                    href="{{ route('surat_keputusan.preview', $h->id) }}?v={{ optional($h->updated_at)->timestamp }}"
                                                    data-url="{{ route('surat_keputusan.preview', $h->id) }}?v={{ optional($h->updated_at)->timestamp }}">
                                                    <i class="fas fa-eye"></i> Lihat Detail
                                                </a>

                                                {{-- Download PDF --}}
                                                @if (in_array($h->status_surat, ['disetujui', 'terbit', 'arsip']) && $h->signed_pdf_path)
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger"
                                                        href="{{ route('surat_keputusan.downloadPdf', $h->id) }}"
                                                        target="_blank">
                                                        <i class="fas fa-download"></i> Download PDF
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Quick View Modal --}}
        <div class="modal fade" id="quickViewModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pratinjau Surat Keputusan</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body p-0" style="position:relative;">
                        <div class="spinner-border text-primary quickview-spinner"></div>
                        <iframe src="about:blank" style="width:100%; border:none; min-height:70vh"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();

            const debounce = (fn, d = 200) => {
                let t;
                return (...a) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn(...a), d);
                };
            };

            const $table = $('#table-sk-saya');
            const th = $table.find('thead th').map((i, el) => $(el).text().trim().toLowerCase()).get();
            const statusIdx = th.indexOf('status');
            const aksiIdx = th.length - 1;

            const table = $table.DataTable({
                responsive: true,
                autoWidth: false,
                language: {
                    url: "/assets/datatables/i18n/id.json",
                    emptyTable: "Tidak ada Surat Keputusan untuk Anda."
                },
                order: [
                    [th.indexOf('tgl surat') !== -1 ? th.indexOf('tgl surat') : 0, 'desc']
                ],
                columnDefs: [{
                    targets: [aksiIdx],
                    orderable: false,
                    searchable: false
                }]
            });

            // Filter
            $('#globalSearch').on('keyup', debounce(function() {
                table.search(this.value).draw();
            }, 200));

            $('#statusFilter').on('change', function() {
                if (statusIdx === -1) return;
                const v = this.value;
                table.column(statusIdx).search(v ? '^' + v + '$' : '', true, false).draw();
            });

            $('#resetFilters').on('click', function(e) {
                e.preventDefault();
                $('#globalSearch, #statusFilter').val('');
                table.search('').columns().search('').draw();
            });

            // Quick View
            $table.on('click', '.quick-view', function(e) {
                e.preventDefault();
                const url = $(this).data('url') || $(this).attr('href');
                const $m = $('#quickViewModal');
                const $sp = $m.find('.quickview-spinner');
                const $if = $m.find('iframe');

                $sp.show();
                $if.off('load').on('load', () => $sp.hide());
                $if.attr('src', url);
                $m.modal('show');
            });

            $('#quickViewModal').on('hidden.bs.modal', function() {
                $(this).find('iframe').off('load').attr('src', 'about:blank');
                $(this).find('.quickview-spinner').hide();
            });

            @if (session('success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    timer: 2500,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endpush
