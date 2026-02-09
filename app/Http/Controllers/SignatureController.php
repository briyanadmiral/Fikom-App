<?php

namespace App\Http\Controllers;

use App\Models\UserSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    /**
     * Show signature capture form
     */
    public function edit()
    {
        $user = Auth::user();
        $signature = UserSignature::where('pengguna_id', $user->id)->first();

        return view('signatures.edit', compact('user', 'signature'));
    }

    /**
     * Store/update signature
     */
    public function update(Request $request)
    {
        $request->validate([
            'signature_data' => 'required_without:signature_file|string',
            'signature_file' => 'required_without:signature_data|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $user = Auth::user();
        $path = null;

        // Handle base64 signature from pad
        if ($request->filled('signature_data')) {
            $data = $request->input('signature_data');

            // Remove data URL prefix
            if (preg_match('/^data:image\/(\w+);base64,/', $data, $matches)) {
                $data = substr($data, strpos($data, ',') + 1);
                $extension = $matches[1];
            } else {
                $extension = 'png';
            }

            $data = base64_decode($data);

            if ($data === false) {
                return back()->withErrors(['signature_data' => 'Data tanda tangan tidak valid.']);
            }

            // Save to private storage
            $filename = 'ttd_'.$user->id.'_'.time().'.'.$extension;
            $path = 'private/ttd/'.$filename;

            Storage::disk('local')->put($path, $data);
        }

        // Handle file upload
        if ($request->hasFile('signature_file')) {
            $file = $request->file('signature_file');
            $filename = 'ttd_'.$user->id.'_'.time().'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('private/ttd', $filename, 'local');
        }

        // Update or create signature record
        $signature = UserSignature::updateOrCreate(
            ['pengguna_id' => $user->id],
            [
                'ttd_path' => $path,
            ]
        );

        // Delete old file if exists and different
        if ($signature->wasRecentlyCreated === false && $signature->getOriginal('ttd_path') !== $path) {
            $oldPath = $signature->getOriginal('ttd_path');
            if ($oldPath && Storage::disk('local')->exists($oldPath)) {
                Storage::disk('local')->delete($oldPath);
            }
        }

        return redirect()->back()->with('success', 'Tanda tangan berhasil disimpan.');
    }

    /**
     * Delete signature
     */
    public function destroy()
    {
        $user = Auth::user();
        $signature = UserSignature::where('pengguna_id', $user->id)->first();

        if ($signature) {
            if ($signature->ttd_path && Storage::disk('local')->exists($signature->ttd_path)) {
                Storage::disk('local')->delete($signature->ttd_path);
            }
            $signature->delete();
        }

        return redirect()->back()->with('success', 'Tanda tangan berhasil dihapus.');
    }

    /**
     * Preview signature image
     */
    public function preview()
    {
        $user = Auth::user();
        $signature = UserSignature::where('pengguna_id', $user->id)->first();

        if (! $signature || ! $signature->ttd_path || ! Storage::disk('local')->exists($signature->ttd_path)) {
            abort(404);
        }

        $file = Storage::disk('local')->get($signature->ttd_path);
        $mimeType = Storage::disk('local')->mimeType($signature->ttd_path) ?? 'image/png';

        return response($file)->header('Content-Type', $mimeType);
    }
}
