<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\NotificationPreference;
use App\Models\TugasHeader;
use App\Models\KeputusanHeader;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendWeeklyDigest extends Command
{
    protected $signature = 'surat:weekly-digest';
    protected $description = 'Send weekly digest email to users who opted in';

    public function handle()
    {
        $this->info('Starting weekly digest...');

        $preferences = NotificationPreference::where('email_digest_weekly', true)
            ->with('user')
            ->get();

        $weekAgo = Carbon::now()->subWeek();
        $sentCount = 0;

        foreach ($preferences as $pref) {
            $user = $pref->user;
            if (!$user || !$user->email) {
                continue;
            }

            // Gather stats for this user
            $digestData = $this->gatherUserDigest($user, $weekAgo);

            if ($digestData['total_activity'] === 0) {
                continue; // Skip if no activity
            }

            // Send email
            try {
                $this->sendDigestEmail($user, $digestData);
                $sentCount++;
                $this->info("Sent digest to: {$user->email}");
            } catch (\Exception $e) {
                $this->error("Failed to send to {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Weekly digest completed. Sent: {$sentCount} emails.");
        return Command::SUCCESS;
    }

    protected function gatherUserDigest(User $user, Carbon $since): array
    {
        $data = [
            'week_start' => $since->format('d M Y'),
            'week_end' => Carbon::now()->format('d M Y'),
            'total_activity' => 0,
        ];

        // ST stats
        $stQuery = TugasHeader::where('dibuat_oleh', $user->id)
            ->where('created_at', '>=', $since);

        $data['st_created'] = (clone $stQuery)->count();
        $data['st_approved'] = TugasHeader::where('dibuat_oleh', $user->id)
            ->where('status_surat', 'disetujui')
            ->where('approved_at', '>=', $since)
            ->count();
        $data['st_rejected'] = TugasHeader::where('dibuat_oleh', $user->id)
            ->where('status_surat', 'ditolak')
            ->where('updated_at', '>=', $since)
            ->count();

        // SK stats
        $skQuery = KeputusanHeader::where('dibuat_oleh', $user->id)
            ->where('created_at', '>=', $since);

        $data['sk_created'] = (clone $skQuery)->count();
        $data['sk_approved'] = KeputusanHeader::where('dibuat_oleh', $user->id)
            ->where('status_surat', 'disetujui')
            ->where('approved_at', '>=', $since)
            ->count();

        // Pending approval (for approvers)
        if ($user->canApproveSurat()) {
            $data['pending_st'] = TugasHeader::where('status_surat', 'pending')->count();
            $data['pending_sk'] = KeputusanHeader::where('status_surat', 'pending')->count();
        } else {
            $data['pending_st'] = 0;
            $data['pending_sk'] = 0;
        }

        $data['total_activity'] = $data['st_created'] + $data['st_approved'] + 
                                   $data['sk_created'] + $data['sk_approved'];

        return $data;
    }

    protected function sendDigestEmail(User $user, array $data): void
    {
        // Simple mail - you can create a Mailable class for better formatting
        Mail::raw(
            $this->buildEmailContent($user, $data),
            function ($message) use ($user) {
                $message->to($user->email, $user->nama_lengkap)
                    ->subject('[SIEGA] Ringkasan Mingguan - ' . Carbon::now()->format('d M Y'));
            }
        );
    }

    protected function buildEmailContent(User $user, array $data): string
    {
        $lines = [
            "Halo {$user->nama_lengkap},",
            "",
            "Berikut ringkasan aktivitas surat Anda minggu ini ({$data['week_start']} - {$data['week_end']}):",
            "",
            "SURAT TUGAS:",
            "- Dibuat: {$data['st_created']}",
            "- Disetujui: {$data['st_approved']}",
            "- Ditolak: {$data['st_rejected']}",
            "",
            "SURAT KEPUTUSAN:",
            "- Dibuat: {$data['sk_created']}",
            "- Disetujui: {$data['sk_approved']}",
            "",
        ];

        if ($data['pending_st'] > 0 || $data['pending_sk'] > 0) {
            $lines[] = "MENUNGGU PERSETUJUAN ANDA:";
            $lines[] = "- Surat Tugas: {$data['pending_st']}";
            $lines[] = "- Surat Keputusan: {$data['pending_sk']}";
            $lines[] = "";
        }

        $lines[] = "---";
        $lines[] = "Sistem Surat SIEGA - FIKOM UNIKA Soegijapranata";
        $lines[] = "Untuk mengubah preferensi notifikasi, kunjungi menu Pengaturan Akun.";

        return implode("\n", $lines);
    }
}
