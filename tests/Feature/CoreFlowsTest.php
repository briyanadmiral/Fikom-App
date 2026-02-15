<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class CoreFlowsTest extends TestCase
{
    public function test_dashboard_loads_for_existing_user(): void
    {
        $user = User::first();

        if (! $user) {
            $this->markTestSkipped('Tidak ada user di database untuk pengujian dashboard.');
        }

        $response = $this->actingAs($user)->get('/home');

        $response->assertStatus(200);
    }

    public function test_surat_tugas_index_loads_for_admin_tu(): void
    {
        $admin = User::where('peran_id', 1)->first();

        if (! $admin) {
            $this->markTestSkipped('Tidak ada user Admin TU (peran_id=1) di database.');
        }

        $response = $this->actingAs($admin)->get(route('surat_tugas.index'));

        $response->assertStatus(200);
    }

    public function test_surat_keputusan_index_loads_for_admin_tu(): void
    {
        $admin = User::where('peran_id', 1)->first();

        if (! $admin) {
            $this->markTestSkipped('Tidak ada user Admin TU (peran_id=1) di database.');
        }

        $response = $this->actingAs($admin)->get(route('surat_keputusan.index'));

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_surat_tugas_archive(): void
    {
        $user = User::where('peran_id', '!=', 1)->first();

        if (! $user) {
            $this->markTestSkipped('Tidak ada user non Admin TU di database.');
        }

        $response = $this->actingAs($user)->get(route('surat_tugas.arsipList'));

        $response->assertStatus(403);
    }

    public function test_non_admin_cannot_access_surat_keputusan_archive(): void
    {
        $user = User::where('peran_id', '!=', 1)->first();

        if (! $user) {
            $this->markTestSkipped('Tidak ada user non Admin TU di database.');
        }

        $response = $this->actingAs($user)->get(route('surat_keputusan.arsipList'));

        $response->assertStatus(403);
    }
}
