<?php

namespace App\Services;

use App\Models\KeputusanHeader;
use App\Models\MasterKopSurat;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class SkPdfService
{
    // Ambil b64 ttd/cap + nilai default
    private function getSigningAssets(KeputusanHeader $sk): array
    {
        $ttdImageB64 = null;
        $penandatangan = $sk->penandatanganUser;
        if ($penandatangan && $penandatangan->signature && !empty($penandatangan->signature->ttd_path)) {
            $ttdImageB64 = $this->b64FromStorage($penandatangan->signature->ttd_path);
        }

        $capImageB64 = null;
        $kop = MasterKopSurat::query()->first();
        if ($kop && !empty($kop->cap_path)) {
            $capImageB64 = $this->b64FromPublicOrStorage($kop->cap_path);
        }

        $ttdW = $sk->ttd_w_mm ?? ($penandatangan?->signature?->default_width_mm ?? 42);
        $capW = $sk->cap_w_mm ?? 35;
        $capOpacity = $sk->cap_opacity ?? 0.95;

        return compact('ttdImageB64','capImageB64','ttdW','capW','capOpacity','kop');
    }

    private function b64FromStorage(?string $path): ?string
    {
        if (!$path) return null;
        if (Storage::disk('local')->exists($path)) {
            $raw = Storage::disk('local')->get($path);
            return 'data:image/png;base64,' . base64_encode($raw);
        }
        return null;
    }

    private function b64FromPublicOrStorage(?string $path): ?string
    {
        if (!$path) return null;
        if (Storage::exists('public/'.ltrim($path,'/'))) {
            $raw = Storage::get('public/'.ltrim($path,'/'));
            return 'data:image/png;base64,' . base64_encode($raw);
        }
        if (Storage::disk('local')->exists($path)) {
            $raw = Storage::disk('local')->get($path);
            return 'data:image/png;base64,' . base64_encode($raw);
        }
        return null;
    }

    public function renderAndStore(KeputusanHeader $sk): string
    {
        $assets = $this->getSigningAssets($sk);

        $html = view('surat_keputusan.surat_pdf', array_merge(
            ['sk' => $sk], $assets, ['showSigns' => true, 'context' => 'pdf']
        ))->render();

        $bytes = Pdf::loadHTML($html)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'dpi'                  => 96,
                'chroot'               => public_path(),
            ])->output();

        $path = "private/surat_keputusan/signed/{$sk->id}_" . md5((string)($sk->nomor ?? '')) . ".pdf";
        Storage::disk('local')->put($path, $bytes);

        return $path;
    }
}
