@php
  use Carbon\Carbon;
@endphp

@component('mail::message')
# Surat Keputusan Disetujui

Yth. {{ $sk->pembuat->nama_lengkap ?? 'Bapak/Ibu' }},

Surat Keputusan **{{ $sk->nomor ?? '(tanpa nomor)' }}** telah **disetujui**.

@component('mail::panel')
- **Tentang**: {{ $sk->tentang }}
- **Tanggal Surat**: {{ optional($sk->tanggal_surat)->isoFormat('D MMMM Y') }}
- **Status**: {{ strtoupper($sk->status_surat) }}
@endcomponent

File PDF **diterlampir**.  
Jika lampiran tidak terlihat, Anda dapat mengunduhnya dari sistem.

Terima kasih.

@endcomponent
