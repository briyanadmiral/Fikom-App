<?php

namespace App\Http\Controllers;

class RedirectController extends Controller
{
    public function toApproveListSt() { return redirect()->route('surat_tugas.approveList'); }
    public function toApproveListSk() { return redirect()->route('surat_keputusan.approveList'); }

    public function legacySt(string $any = null)
    {
        $to = '/surat_tugas' . ($any ? "/{$any}" : '');
        $qs = request()->getQueryString();
        return redirect()->to($qs ? "{$to}?{$qs}" : $to, 301);
    }

    public function legacySk(string $any = null)
    {
        $to = '/surat_keputusan' . ($any ? "/{$any}" : '');
        $qs = request()->getQueryString();
        return redirect()->to($qs ? "{$to}?{$qs}" : $to, 301);
    }
}
