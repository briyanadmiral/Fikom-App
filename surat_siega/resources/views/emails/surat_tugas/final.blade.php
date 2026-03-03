@component('mail::message')
# Surat Tugas Final

Yth. Bapak/Ibu,

Surat Tugas **{{ $tugas->nomor }}** ({{ $tugas->tugas }}) telah disetujui.

@component('mail::panel')
- **Tanggal Surat**: {{ optional(\Carbon\Carbon::parse($tugas->tanggal_surat ?? null))->isoFormat('D MMMM Y') }}
- **Waktu**: {{ $tugas->waktu_mulai }} s.d. {{ $tugas->waktu_selesai }}
@if(!empty($tugas->tempat))
- **Tempat**: {{ $tugas->tempat }}
@endif
@endcomponent

@if(!empty($tugas->redaksi_pembuka))
{{ $tugas->redaksi_pembuka }}
@endif

@if(!empty($tugas->penutup))
{{ $tugas->penutup }}
@endif

Terima kasih.

@endcomponent
