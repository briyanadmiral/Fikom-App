<?php

namespace App\Services;

use App\Models\KeputusanHeader;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Mews\Purifier\Facades\Purifier;

/**
 * SuratKeputusanService - Enhanced security dengan validation & error handling.
 */
class SuratKeputusanService
{
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
        try {
            // Validate status
            $status = validate_status($status, ['draft', 'pending']);
            if ($status === null) {
                throw new \InvalidArgumentException('Status tidak valid');
            }

            return DB::transaction(function () use ($validatedData, $status) {
                $data = $this->prepareDataForSave($validatedData);
                $data['status_surat'] = $status;
                if (isset($data['status'])) {
                    unset($data['status']);
                } // Prevent unknown column error

                // Validate auth user
                $userId = validate_integer_id(Auth::id());
                if ($userId === null) {
                    throw new \RuntimeException('User tidak terautentikasi');
                }

                $data['dibuat_oleh'] = $userId;

                $penerimaInternalIds = $data['penerima_internal'] ?? [];
                unset($data['penerima_internal']);

                // Validate penerima IDs
                $validatedIds = $this->validatePenerimaIds($penerimaInternalIds);

                $sk = KeputusanHeader::create($data);

                if (method_exists($sk, 'penerima') && ! empty($validatedIds)) {
                    $sk->penerima()->sync($validatedIds);
                }

                // Log creation
                Log::info('Surat Keputusan created', [
                    'sk_id' => $sk->id,
                    'status' => $status,
                    'dibuat_oleh' => $userId,
                ]);

                if ($status === 'pending') {
                    $this->notificationService->notifyApprovalRequest($sk);
                }

                return $sk;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create Surat Keputusan', [
                'status' => $status,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            throw $e;
        }
    }

    /**
     * Memperbarui Surat Keputusan yang ada.
     */
    public function updateKeputusan(KeputusanHeader $sk, array $validatedData, ?string $newStatus): KeputusanHeader
    {
        try {
            // Validate SK ID
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                throw new \InvalidArgumentException('SK ID tidak valid');
            }

            // Validate new status
            if ($newStatus !== null) {
                $newStatus = validate_status($newStatus, ['draft', 'pending', 'disetujui', 'ditolak']);
                if ($newStatus === null) {
                    throw new \InvalidArgumentException('Status baru tidak valid');
                }
            }

            return DB::transaction(function () use ($sk, $validatedData, $newStatus, $skId) {
                $wasPending = $sk->status_surat === 'pending';

                $data = $this->prepareDataForSave($validatedData);
                if ($newStatus) {
                    $data['status_surat'] = $newStatus;
                }

                $penerimaInternalIds = $data['penerima_internal'] ?? [];
                unset($data['penerima_internal']);

                // Validate penerima IDs
                $validatedIds = $this->validatePenerimaIds($penerimaInternalIds);

                $sk->update($data);

                if (method_exists($sk, 'penerima')) {
                    $sk->penerima()->sync($validatedIds);
                }

                $sk->refresh();

                // Log update
                Log::info('Surat Keputusan updated', [
                    'sk_id' => $skId,
                    'old_status' => $wasPending ? 'pending' : 'other',
                    'new_status' => $sk->status_surat,
                ]);

                if ($newStatus === 'pending') {
                    $this->notificationService->notifyApprovalRequest($sk);
                } elseif ($wasPending && $sk->status_surat === 'pending') {
                    $this->notificationService->notifyRevised($sk, auth()->user());
                }

                return $sk;
            });
        } catch (\Exception $e) {
            Log::error('Failed to update Surat Keputusan', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            throw $e;
        }
    }

    /**
     * Menyetujui SK, menghasilkan nomor, dan menyimpan data.
     */
    public function approveAndGenerateNumber(KeputusanHeader $sk, array $approvalData): KeputusanHeader
    {
        try {
            // Validate SK ID
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                throw new \InvalidArgumentException('SK ID tidak valid');
            }

            // Validate user
            $userId = validate_integer_id(Auth::id());
            if ($userId === null) {
                throw new \RuntimeException('User tidak terautentikasi');
            }

            return DB::transaction(function () use ($sk, $approvalData, $skId, $userId) {
                // Generate nomor jika belum ada
                if (empty($sk->nomor)) {
                    $referenceDate = $sk->tanggal_surat ?? $sk->created_at;
                    $date = now()->parse($referenceDate);

                    // Validate approval data
                    $unit = sanitize_alphanumeric($approvalData['unit'] ?? 'FIKOM');
                    $kodeKlasifikasi = sanitize_kode($approvalData['kode_klasifikasi'] ?? 'B.10.1');

                    $res = $this->nomorSuratService->reserve($unit, $kodeKlasifikasi, $this->toRoman((int) $date->format('n')), (int) $date->format('Y'));
                    $sk->nomor = $res['nomor'];
                }

                // Fallback tanggal_surat jika masih kosong
                if (empty($sk->tanggal_surat)) {
                    $sk->tanggal_surat = now()->toDateString();
                }

                // Validate dimensions & opacity
                $ttdW = $this->validateDimension($approvalData['ttd_w_mm'] ?? 42, 20, 80);
                $capW = $this->validateDimension($approvalData['cap_w_mm'] ?? 35, 20, 80);
                $capOpacity = $this->validateOpacity($approvalData['cap_opacity'] ?? 0.95);

                $sk->fill([
                    'ttd_w_mm' => $ttdW,
                    'cap_w_mm' => $capW,
                    'cap_opacity' => $capOpacity,
                    'status_surat' => 'disetujui',
                    'approved_by' => $userId,
                    'approved_at' => now(),
                    'penandatangan' => $userId,
                    'signed_at' => now(),
                ]);

                $sk->save();

                // Log approval
                Log::info('Surat Keputusan approved', [
                    'sk_id' => $skId,
                    'nomor' => sanitize_log_message($sk->nomor),
                    'approved_by' => $userId,
                ]);

                $this->notificationService->notifyApproved($sk);

                return $sk;
            });
        } catch (\Exception $e) {
            Log::error('Failed to approve Surat Keputusan', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            throw $e;
        }
    }

    /**
     * Prepare data untuk save.
     */
    private function prepareDataForSave(array $data): array
    {
        // Build HTML memutuskan dari menetapkan
        if (! empty($data['menetapkan']) && is_array($data['menetapkan'])) {
            foreach ($data['menetapkan'] as &$d) {
                $d['isi'] = Purifier::clean($d['isi'] ?? '');
            }
            $data['memutuskan'] = $this->buildMemutuskanHtml($data['menetapkan']);
        }

        // Cleanup: hapus field yang tidak perlu
        unset($data['mode'], $data['tembusan_formatted']);

        // Handle penerima_eksternal (jika kolom ada di DB)
        if (Schema::hasColumn('keputusan_header', 'penerima_eksternal') && isset($data['penerima_eksternal'])) {
            $data['penerima_eksternal'] = sanitize_input($data['penerima_eksternal'], 1000);
        } else {
            unset($data['penerima_eksternal']);
        }

        // Validate penandatangan ID
        if (isset($data['penandatangan'])) {
            $data['penandatangan'] = validate_integer_id($data['penandatangan']);
        }

        // Return data yang sudah bersih
        $result = [
            'nomor' => $data['nomor'] ?? null,
            'tentang' => $data['tentang'],
            'judul_penetapan' => $data['judul_penetapan'] ?? null,
            'tanggal_surat' => $data['tanggal_surat'],
            'kota_penetapan' => $data['kota_penetapan'] ?? 'Semarang',
            'tahun' => $data['tahun'] ?? null,
            'penandatangan' => $data['penandatangan'] ?? null,
            'npp_penandatangan' => $data['npp_penandatangan'] ?? null,
            'tembusan' => $data['tembusan'] ?? null,
        ];

        // Only include array fields if they are present to avoid overwriting with empty arrays
        $arrayFields = ['menimbang', 'mengingat', 'menetapkan', 'penerima_internal', 'penerima_eksternal'];
        foreach ($arrayFields as $field) {
            if (array_key_exists($field, $data)) {
                $result[$field] = $data[$field];
            }
        }

        // Handle memutuskan (derived from menetapkan)
        if (array_key_exists('memutuskan', $data)) {
             $result['memutuskan'] = $data['memutuskan'];
        }

        return $result;
    }

    /**
     * Build HTML untuk kolom memutuskan dari array menetapkan.
     */
    private function buildMemutuskanHtml(?array $menetapkan): string
    {
        $menetapkan = $menetapkan ?? [];
        if (empty($menetapkan)) {
            return '';
        }

        $parts = [];
        foreach ($menetapkan as $d) {
            $judul = strtoupper(trim($d['judul'] ?? ''));
            $isi = $d['isi'] ?? '';

            if ($judul === '' && trim(strip_tags($isi)) === '') {
                continue;
            }

            $parts[] = '<p><strong>'.e($judul).':</strong> '.$isi.'</p>';
        }

        return implode("\n", $parts);
    }

    /**
     * Convert angka ke Roman numeral untuk bulan.
     */
    private function toRoman(int $number): string
    {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];

        $ret = '';
        $number = max(0, min(3999, (int) $number)); // Boundary check

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

    /**
     * Reject Surat Keputusan.
     */
    public function rejectKeputusan(KeputusanHeader $sk, string $note): KeputusanHeader
    {
        try {
            // Validate SK ID
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                throw new \InvalidArgumentException('SK ID tidak valid');
            }

            // Validate user
            $userId = validate_integer_id(Auth::id());
            if ($userId === null) {
                throw new \RuntimeException('User tidak terautentikasi');
            }

            // Sanitize note
            $sanitizedNote = sanitize_input($note, 500);

            $sk->update([
                'status_surat' => 'ditolak',
                'rejected_by' => $userId,
                'rejected_at' => now(),
            ]);

            // Log rejection
            Log::info('Surat Keputusan rejected', [
                'sk_id' => $skId,
                'rejected_by' => $userId,
                'note' => sanitize_log_message($sanitizedNote ?? '(no note)'),
            ]);

            $this->notificationService->notifyRejected($sk, $sanitizedNote);

            return $sk;
        } catch (\Exception $e) {
            Log::error('Failed to reject Surat Keputusan', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            throw $e;
        }
    }

    /**
     * Submit SK untuk approval.
     */
    public function submitForApproval(KeputusanHeader $sk): KeputusanHeader
    {
        try {
            // Validate SK ID
            $skId = validate_integer_id($sk->id);
            if ($skId === null) {
                throw new \InvalidArgumentException('SK ID tidak valid');
            }

            $sk->update(['status_surat' => 'pending']);

            // Log submission
            Log::info('Surat Keputusan submitted for approval', [
                'sk_id' => $skId,
                'submitted_by' => Auth::id(),
            ]);

            $this->notificationService->notifyApprovalRequest($sk);

            return $sk;
        } catch (\Exception $e) {
            Log::error('Failed to submit Surat Keputusan for approval', [
                'sk_id' => $sk->id ?? null,
                'error' => sanitize_log_message($e->getMessage()),
            ]);
            throw $e;
        }
    }

    /**
     * Validate penerima IDs.
     */
    private function validatePenerimaIds(array $ids): array
    {
        $validatedIds = [];

        foreach ($ids as $id) {
            $validId = validate_integer_id($id);
            if ($validId !== null) {
                $validatedIds[] = $validId;
            }
        }

        return $validatedIds;
    }

    /**
     * Validate dimension value.
     */
    private function validateDimension($value, int $min, int $max): int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        if ($value === false || $value < $min || $value > $max) {
            return intdiv($min + $max, 2); // Return middle value as default
        }

        return $value;
    }

    /**
     * Validate opacity value.
     */
    private function validateOpacity($value): float
    {
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);

        if ($value === false || $value < 0 || $value > 1) {
            return 0.95; // Default
        }

        return $value;
    }
}
