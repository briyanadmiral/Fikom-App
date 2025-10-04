{{-- resources/views/pengaturan/kop_surat.blade.php --}}
@extends('layouts.app')

@section('title','Pengaturan Kop Surat')

@section('content')
<div class="container-fluid">
    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <i class="fas fa-check mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <i class="fas fa-ban mr-1"></i> Gagal menyimpan. Periksa isian di bawah:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        {{-- === KIRI: FORM PENGATURAN === --}}
        <div class="col-lg-7">
            <div class="card card-primary">
                <div class="card-header">
                    <i class="fas fa-tools mr-2"></i>Pengaturan Kop & Cap
                </div>
                <div class="card-body">
                    <form action="{{ route('kop.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label class="font-weight-bold">Mode Kop</label>
                            <select name="mode" id="mode" class="form-control">
                                <option value="image" {{ ($kop->mode ?? 'image')==='image' ? 'selected' : '' }}>
                                    Gambar penuh (fallback)
                                </option>
                                <option value="composed" {{ ($kop->mode ?? 'image')==='composed' ? 'selected' : '' }}>
                                    Composed (teks + logo)
                                </option>
                            </select>
                            <small class="text-muted">
                                Pilih <b>Composed</b> untuk mengisi judul, alamat, dst. Layout tetap kanan-atas.
                            </small>
                        </div>

                        {{-- Field untuk mode composed --}}
                        <div class="border rounded p-3 mb-3">
                            <div class="form-row">
                                <div class="form-group col-md-6" data-composed>
                                    <label>Judul Atas</label>
                                    <input name="judul_atas" class="form-control"
                                           value="{{ old('judul_atas', $kop->judul_atas) }}">
                                </div>
                                <div class="form-group col-md-6" data-composed>
                                    <label>Subjudul</label>
                                    <input name="subjudul" class="form-control"
                                           value="{{ old('subjudul', $kop->subjudul) }}">
                                </div>
                                <div class="form-group col-md-12" data-composed>
                                    <label>Alamat</label>
                                    <input name="alamat" class="form-control"
                                           value="{{ old('alamat', $kop->alamat) }}">
                                </div>
                                <div class="form-group col-md-4" data-composed>
                                    <label>Telepon</label>
                                    <input name="telepon" class="form-control"
                                           value="{{ old('telepon', $kop->telepon) }}">
                                </div>
                                <div class="form-group col-md-4" data-composed>
                                    <label>Fax</label>
                                    <input name="fax" class="form-control"
                                           value="{{ old('fax', $kop->fax) }}">
                                </div>
                                <div class="form-group col-md-4" data-composed>
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control"
                                           value="{{ old('email', $kop->email) }}">
                                </div>
                                <div class="form-group col-md-6" data-composed>
                                    <label>Website</label>
                                    <input name="website" class="form-control"
                                           value="{{ old('website', $kop->website) }}">
                                </div>

                                <div class="form-group col-md-6" data-composed>
                                    <label>Logo Kiri</label>
                                    <input type="file" name="logo_kiri" class="form-control-file">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="tampilkan_logo_kiri" value="1"
                                               {{ !empty($kop?->tampilkan_logo_kiri) ? 'checked' : '' }}>
                                        <label class="form-check-label">Tampilkan logo kiri</label>
                                    </div>
                                    @if($kop?->logo_kiri_path)
                                        <img src="{{ asset('storage/'.$kop->logo_kiri_path) }}" class="mt-2 img-thumbnail" style="max-height:60px">
                                    @endif
                                </div>

                                <div class="form-group col-md-6" data-composed>
                                    <label>Logo Kanan</label>
                                    <input type="file" name="logo_kanan" class="form-control-file">
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" name="tampilkan_logo_kanan" value="1"
                                               {{ !empty($kop?->tampilkan_logo_kanan) ? 'checked' : '' }}>
                                        <label class="form-check-label">Tampilkan logo kanan</label>
                                    </div>
                                    @if($kop?->logo_kanan_path)
                                        <img src="{{ asset('storage/'.$kop->logo_kanan_path) }}" class="mt-2 img-thumbnail" style="max-height:60px">
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Upload fallback (image mode) & cap --}}
                        <div class="border rounded p-3 mb-3">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Header (PNG/JPG) – fallback</label>
                                    <input type="file" name="header" class="form-control-file">
                                    @if($kop?->header_path)
                                        <img src="{{ asset('storage/'.$kop->header_path) }}" class="mt-2 img-thumbnail" style="max-height:80px">
                                    @endif
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Footer (PNG/JPG)</label>
                                    <input type="file" name="footer" class="form-control-file">
                                    @if($kop?->footer_path)
                                        <img src="{{ asset('storage/'.$kop->footer_path) }}" class="mt-2 img-thumbnail" style="max-height:80px">
                                    @endif
                                </div>
                                <div class="form-group col-md-4">
                                    <label>Cap/Stamp (PNG transparan)</label>
                                    <input type="file" name="cap" class="form-control-file">
                                    @if($kop?->cap_path)
                                        <img src="{{ asset('storage/'.$kop->cap_path) }}" class="mt-2 img-thumbnail" style="max-height:80px">
                                    @endif
                                </div>
                            </div>
                        </div>

                        <button class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Simpan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- === KANAN: PRATINJAU === --}}
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-eye mr-2"></i>Pratinjau Kop (Tampilan Ringkas)
                </div>
                <div class="card-body">
                    <style>
                        .kop-kanan-preview { position: relative; width: 420px; margin-left: auto; text-align: right; }
                        .kop-kanan-preview img.logo { height: 56px; vertical-align: middle; }
                        .kop-kanan-preview .title { font-weight: 700; letter-spacing: .02em; font-size: 16px; }
                        .kop-kanan-preview .meta { font-size: 11px; line-height: 1.25; }
                    </style>

                    @if(($kop->mode ?? 'image') === 'image' && !empty($kop?->header_path))
                        <div class="kop-kanan-preview">
                            <img class="logo" src="{{ asset('storage/'.$kop->header_path) }}">
                        </div>
                    @else
                        <div class="kop-kanan-preview">
                            @if(($kop->tampilkan_logo_kanan ?? false) && $kop->logo_kanan_path)
                                <img class="logo" src="{{ asset('storage/'.$kop->logo_kanan_path) }}">
                            @endif
                            <div class="title">{{ strtoupper($kop->judul_atas ?? '') ?: '—' }}</div>
                            <div class="meta">
                                {{ $kop->subjudul ?: '—' }}<br>
                                {{ $kop->alamat ?: '—' }}<br>
                                Telp. {{ $kop->telepon ?: '—' }}@if($kop?->fax), Fax. {{ $kop->fax }}@endif<br>
                                email: {{ $copied = $kop->email ?: '—' }}@if($kop?->website) | {{ $kop->website }} @endif
                            </div>
                        </div>
                    @endif

                    <small class="text-muted d-block mt-3">
                        *Pratinjau ini hanya header; tata letak final mengikuti PDF.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toggle enable/disable field composed --}}
@push('scripts')
<script>
(function(){
    function toggleComposed(){
        var useComposed = document.getElementById('mode').value === 'composed';
        document.querySelectorAll('[data-composed] input, [data-composed] textarea, [data-composed] select')
            .forEach(function(el){ el.disabled = !useComposed; });
        document.querySelectorAll('[data-composed]').forEach(function(w){
            w.style.opacity = useComposed ? '1' : '0.6';
        });
    }
    document.addEventListener('DOMContentLoaded', function(){
        toggleComposed();
        document.getElementById('mode').addEventListener('change', toggleComposed);
    });
})();
</script>
@endpush
@endsection