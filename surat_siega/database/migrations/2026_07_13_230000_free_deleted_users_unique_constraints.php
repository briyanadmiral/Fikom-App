<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Get all soft-deleted users that do not have the '.deleted.' suffix in their email
        $deletedUsers = DB::table('pengguna')
            ->whereNotNull('deleted_at')
            ->where('email', 'not like', '%.deleted.%')
            ->get();

        foreach ($deletedUsers as $user) {
            $timestamp = $user->deleted_at ? strtotime($user->deleted_at) : time();
            $suffix = '.deleted.' . $timestamp;
            $email = $user->email . $suffix;
            $npp = $user->npp ? $user->npp . $suffix : null;

            DB::table('pengguna')
                ->where('id', $user->id)
                ->update([
                    'email' => $email,
                    'npp' => $npp,
                ]);
        }
    }

    public function down(): void
    {
        // No easy way to revert as the suffix contains variable timestamps,
        // but since they are soft-deleted, keeping the suffix is safe.
    }
};
