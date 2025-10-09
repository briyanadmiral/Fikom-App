<?php

namespace App\Services;

use App\Models\KeputusanHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mews\Purifier\Facades\Purifier;

class SuratKeputusanService
{
    // Dua service lain yang kita butuhkan akan di-inject secara otomatis oleh Laravel
    protected SuratKeputusanNotificationService $notificationService;
    protected NomorSuratService $nomorSuratService;

    public function __construct(SuratKeputusanNotificationService $notificationService, NomorSuratService $nomorSuratService)
    {
        $this->notificationService = $notificationService;
        $this->nomorSuratService = $nomorSuratService;
    }

    /**
     * Membuat Surat Keputusan baru dari data yang sudah divalidasi.
     */
    public function createKeputusan(array $validatedData, string $status): KeputusanHeader
    {
        return DB::transaction(function () use ($validatedData, $status) {
            $data = $this->prepareDataForSave($validatedData);
            $data['status_surat'] = $status;
            $data['dibuat_oleh'] = Auth::id();

            $penerimaInternalIds = $data['penerima_internal'] ?? [];
            unset($data['penerima_internal']);

            $sk = KeputusanHeader::create($data);

            if (method_exists($sk, 'penerima') && !empty($penerimaInternalIds)) {
                $sk->penerima()->sync($penerimaInternalIds);
            }

            if ($status === 'pending') {
                $this->notificationService->notifyApprovalRequest($sk);
            }

            return $sk;
        });
    }

    /**
     * Memperbarui Surat Keputusan yang ada.
     */
    public function updateKeputusan(KeputusanHeader $sk, array $validatedData, ?string $newStatus): KeputusanHeader
    {
        return DB::transaction(function () use ($sk, $validatedData, $newStatus) {
            $wasPending = $sk->status_surat === 'pending';

            $data = $this->prepareDataForSave($validatedData);
            if ($newStatus) {
                $data['status_surat'] = $newStatus;
            }

            $penerimaInternalIds = $data['penerima_internal'] ?? [];
            unset($data['penerima_internal']);

            $sk->update($data);

            if (method_exists($sk, 'penerima')) {
                $sk->penerima()->sync($penerimaInternalIds);
            }

            $sk->refresh();

            if ($newStatus === 'pending') {
                $this->notificationService->notifyApprovalRequest($sk);
            } elseif ($wasPending && $sk->status_surat === 'pending') {
                $this->notificationService->notifyRevised($sk, auth()->user());
            }

            return $sk;
        });
    }

    /**
     * Menyetujui SK, menghasilkan nomor, dan menyimpan data.
     */
    public function approveAndGenerateNumber(KeputusanHeader $sk, array $approvalData): KeputusanHeader
    {
        return DB::transaction(function () use ($sk, $approvalData) {
            if (empty($sk->nomor)) {
                $date = now()->parse($sk->tanggal_asli);
                $res = $this->nomorSuratService->reserve(
                    $approvalData['unit'] ?? 'FIKOM',
                    $approvalData['kode_klasifikasi'] ?? 'B.10.1',
                    $this->toRoman((int) $date->format('n')),
                    (int) $date->format('Y')
                );
                $sk->nomor = $res['nomor'];
            }

            if (empty($sk->tanggal_surat)) {
                $sk->tanggal_surat = now()->toDateString();
            }

            $sk->fill([
                'ttd_w_mm' => $approvalData['ttd_w_mm'],
                'cap_w_mm' => $approvalData['cap_w_mm'],
                'cap_opacity' => $approvalData['cap_opacity'],
                'status_surat' => 'disetujui',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'penandatangan' => Auth::id(),
                'signed_at' => now(),
            ]);

            $sk->save();

            $this->notificationService->notifyApproved($sk);

            return $sk;
        });
    }

    private function prepareDataForSave(array $data): array
    {
        if (!empty($data['menetapkan']) && is_array($data['menetapkan'])) {
            foreach ($data['menetapkan'] as &$d) {
                $d['isi'] = Purifier::clean($d['isi'] ?? '');
            }
            $data['memutuskan'] = $this->buildMemutuskanHtml($data['menetapkan']);
        }

        unset($data['mode'], $data['tembusan_formatted']);

        if (Schema::hasColumn('keputusan_header', 'penerima_eksternal') && isset($data['penerima_eksternal'])) {
            $data['penerima_eksternal'] = $data['penerima_eksternal'];
        } else {
            unset($data['penerima_eksternal']);
        }

        return $data;
    }

    private function buildMemutuskanHtml(?array $menetapkan): string
    {
        $menetapkan = $menetapkan ?? [];
        if (empty($menetapkan)) return '';

        $parts = [];
        foreach ($menetapkan as $d) {
            $judul = strtoupper(trim($d['judul'] ?? ''));
            $isi   = $d['isi'] ?? '';
            if ($judul === '' && trim(strip_tags($isi)) === '') continue;
            $parts[] = '<p><strong>' . e($judul) . ':</strong> ' . $isi . '</p>';
        }
        return implode("\n", $parts);
    }

    private function toRoman($number)
    {
        $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90, 'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
        $ret = '';
        $number = max(0, min(3999, (int) $number));
        while ($number > 0) {
            foreach ($map as $roman => $int) {
                if ($number >= $int) {
                    $number -= $int;
                    $ret .= $roman;
                    break;
                }
            }
        }
        return $ret;
    }

    public function rejectKeputusan(KeputusanHeader $sk, string $note): KeputusanHeader
    {
        $sk->update([
            'status_surat' => 'ditolak',
            'rejected_by'  => Auth::id(),
            'rejected_at'  => now(),
        ]);

        $this->notificationService->notifyRejected($sk, $note);
        return $sk;
    }

    public function submitForApproval(KeputusanHeader $sk): KeputusanHeader
    {
        $sk->update(['status_surat' => 'pending']);
        $this->notificationService->notifyApprovalRequest($sk);
        return $sk;
    }
}
