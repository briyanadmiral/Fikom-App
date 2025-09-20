<?php

namespace App\Observers;

use App\Jobs\SendSuratTugasEmail;
use App\Models\TugasHeader;

class TugasHeaderObserver
{
    public function updated(TugasHeader $tugas): void
    {
        // Saat status jadi pending → email ke approver
        if ($tugas->wasChanged('status_surat') && $tugas->status_surat === 'pending' && $tugas->next_approver) {
            SendSuratTugasEmail::dispatch($tugas->id, 'to_approver');
        }

        // Saat PDF final tersedia → kirim ke penerima
        if ($tugas->wasChanged('signed_pdf_path') && $tugas->signed_pdf_path) {
            SendSuratTugasEmail::dispatch($tugas->id, 'to_recipients');
        }
    }
}
