<p>Yth. Bapak/Ibu,</p>
<p>Surat Tugas <strong>{{ $d['nomor'] }}</strong> ({{ $d['tugas'] }}) telah disetujui.</p>

<ul>
  @if($d['tanggal_surat'])<li>Tanggal Surat: {{ \Carbon\Carbon::parse($d['tanggal_surat'])->isoFormat('D MMMM Y') }}</li>@endif
  @if($d['waktu_mulai'] || $d['waktu_selesai'])<li>Waktu: {{ $d['waktu_mulai'] }} s.d. {{ $d['waktu_selesai'] }}</li>@endif
  @if($d['tempat'])<li>Tempat: {{ $d['tempat'] }}</li>@endif
</ul>

@if(!empty($d['pembuka']))<p>{{ $d['pembuka'] }}</p>@endif
@if(!empty($d['penutup']))<p>{{ $d['penutup'] }}</p>@endif

<p>Terima kasih.</p>
