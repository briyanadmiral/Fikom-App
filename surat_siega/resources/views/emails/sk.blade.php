@php($safeNomor = $sk->nomor ?? '(tanpa nomor)')
<!doctype html>
<html>
  <body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#111">
    <div style="max-width:640px;margin:auto;padding:24px">
      <h2 style="margin:0 0 12px 0">{{ $heading }}</h2>
      <p style="margin:0 0 16px 0">
        {{ $messageLine }}
      </p>
      <table role="presentation" style="margin:20px 0">
        <tr><td style="padding:12px 0">
          <div style="font-size:14px;line-height:1.6;color:#333">
            <div><strong>Nomor</strong>: {{ $safeNomor }}</div>
            <div><strong>Tentang</strong>: {{ $sk->tentang }}</div>
            <div><strong>Tanggal Surat</strong>: {{ optional($sk->tanggal_surat)->format('d M Y') ?? '-' }}</div>
            <div><strong>Status</strong>: {{ ucfirst($sk->status_surat) }}</div>
          </div>
        </td></tr>
      </table>

      @if($ctaUrl && $ctaText)
        <p>
          <a href="{{ $ctaUrl }}"
             style="display:inline-block;padding:10px 16px;background:#0d6efd;color:#fff;text-decoration:none;border-radius:8px">
            {{ $ctaText }}
          </a>
        </p>
      @endif

      <p style="margin-top:28px;font-size:12px;color:#666">
        Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.
      </p>
    </div>
  </body>
</html>
