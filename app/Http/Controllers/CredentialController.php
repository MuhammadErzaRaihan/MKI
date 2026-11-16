<?php

namespace App\Http\Controllers;

use App\Models\EmployeeCredential;
use App\Models\SecurityAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CredentialController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->query('q', ''));

        $credentials = EmployeeCredential::query()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where(function ($sub) use ($query) {
                    $sub->where('nama', 'like', "%{$query}%")
                        ->orWhere('nip', 'like', "%{$query}%")
                        ->orWhere('instansi', 'like', "%{$query}%");
                });
            })
            ->latest()
            ->get();

        return response()->json([
            'data' => $credentials,
            'total' => $credentials->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nip' => ['required', 'string', 'max:18', 'unique:employee_credentials,nip'],
            'nama' => ['required', 'string', 'max:150'],
            'instansi' => ['required', 'string', 'max:150'],
            'ttl' => ['required', 'string', 'max:120'],
            'telp' => ['required', 'string', 'max:25'],
            'secret_key' => ['required', 'string', 'max:32', 'unique:employee_credentials,secret_key'],
            'status' => ['nullable', Rule::in(['ON', 'OFF'])],
            'photo_file' => ['nullable', 'image', 'max:2048'],
            'photo_base64' => ['nullable', 'string'],
        ]);

        $clientIp = $request->ip() ?? '127.0.0.1';
        $isOffice = EmployeeCredential::isOfficeNetwork($clientIp);
        $photoPath = $this->persistPhoto($request);

        $credential = EmployeeCredential::create([
            'nip' => $validated['nip'],
            'nama' => $validated['nama'],
            'instansi' => $validated['instansi'],
            'ttl' => $validated['ttl'],
            'telp' => $validated['telp'],
            'status' => $validated['status'] ?? 'ON',
            'ip_detected' => $clientIp,
            'is_office_ip' => $isOffice,
            'secret_key' => $validated['secret_key'],
            'photo_path' => $photoPath,
        ]);

        SecurityAuditLog::create([
            'employee_credential_id' => $credential->id,
            'nip' => $credential->nip,
            'ip_address' => $clientIp,
            'auth_method' => 'SYSTEM',
            'result' => 'ALLOWED',
            'description' => "Registrasi kredensial pegawai {$credential->nama}",
        ]);

        return response()->json([
            'message' => "Kredensial baru untuk {$credential->nama} berhasil disimpan.",
            'data' => $credential,
        ], 201);
    }

    public function update(Request $request, EmployeeCredential $credential): JsonResponse
    {
        $validated = $request->validate([
            'nip' => ['required', 'string', 'max:18', Rule::unique('employee_credentials', 'nip')->ignore($credential->id)],
            'nama' => ['required', 'string', 'max:150'],
            'instansi' => ['required', 'string', 'max:150'],
            'ttl' => ['required', 'string', 'max:120'],
            'telp' => ['required', 'string', 'max:25'],
            'secret_key' => ['required', 'string', 'max:32', Rule::unique('employee_credentials', 'secret_key')->ignore($credential->id)],
            'status' => ['nullable', Rule::in(['ON', 'OFF'])],
            'photo_file' => ['nullable', 'image', 'max:2048'],
            'photo_base64' => ['nullable', 'string'],
        ]);

        $newPhotoPath = $this->persistPhoto($request, $credential->photo_path);

        $credential->update([
            'nip' => $validated['nip'],
            'nama' => $validated['nama'],
            'instansi' => $validated['instansi'],
            'ttl' => $validated['ttl'],
            'telp' => $validated['telp'],
            'status' => $validated['status'] ?? $credential->status,
            'secret_key' => $validated['secret_key'],
            'photo_path' => $newPhotoPath,
        ]);

        SecurityAuditLog::create([
            'employee_credential_id' => $credential->id,
            'nip' => $credential->nip,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'auth_method' => 'SYSTEM',
            'result' => 'UPDATED',
            'description' => "Pembaruan data kredensial pegawai {$credential->nama}",
        ]);

        return response()->json([
            'message' => "Data kredensial {$credential->nama} berhasil diperbarui.",
            'data' => $credential->fresh(),
        ]);
    }

    public function destroy(Request $request, EmployeeCredential $credential): JsonResponse
    {
        $nama = $credential->nama;
        $nip = $credential->nip;

        if ($credential->photo_path && Storage::disk('public')->exists($credential->photo_path)) {
            Storage::disk('public')->delete($credential->photo_path);
        }

        SecurityAuditLog::create([
            'employee_credential_id' => null,
            'nip' => $nip,
            'ip_address' => $request->ip() ?? '127.0.0.1',
            'auth_method' => 'SYSTEM',
            'result' => 'DELETED',
            'description' => "Penghapusan kredensial pegawai {$nama} ({$nip})",
        ]);

        $credential->delete();

        return response()->json([
            'message' => "Kredensial pegawai {$nama} telah dihapus.",
        ]);
    }

    // Enforces SPBE access rule: office subnet uses OTP, external IP requires Secret Key.
    public function verifyAccess(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nip' => ['required', 'string', 'max:18'],
            'otp_code' => ['nullable', 'string', 'max:10'],
            'secret_key' => ['nullable', 'string', 'max:32'],
            'simulated_ip' => ['nullable', 'ip'],
        ]);

        $ip = $validated['simulated_ip'] ?? ($request->ip() ?? '127.0.0.1');
        $isOffice = EmployeeCredential::isOfficeNetwork($ip);
        $credential = EmployeeCredential::where('nip', $validated['nip'])->first();

        if (! $credential || $credential->status !== 'ON') {
            SecurityAuditLog::create([
                'employee_credential_id' => $credential?->id,
                'nip' => $validated['nip'],
                'ip_address' => $ip,
                'auth_method' => $isOffice ? 'OTP' : 'SECRET_KEY',
                'result' => 'BLOCKED',
                'description' => 'NIP tidak ditemukan atau status kredensial nonaktif',
            ]);

            return response()->json([
                'authenticated' => false,
                'message' => 'Akses ditolak: NIP tidak terdaftar atau status kredensial OFF.',
            ], 403);
        }

        if ($isOffice) {
            $validOtp = ! empty($validated['otp_code']) && strlen($validated['otp_code']) === 6;

            SecurityAuditLog::create([
                'employee_credential_id' => $credential->id,
                'nip' => $credential->nip,
                'ip_address' => $ip,
                'auth_method' => 'OTP',
                'result' => $validOtp ? 'ALLOWED' : 'BLOCKED',
                'description' => $validOtp
                    ? 'Verifikasi OTP berhasil dalam lingkup IP Kantor'
                    : 'Kode OTP tidak valid pada akses IP Kantor',
            ]);

            if (! $validOtp) {
                return response()->json([
                    'authenticated' => false,
                    'required_method' => 'OTP',
                    'message' => 'IP terdeteksi di lingkungan kantor. Masukkan 6 digit Kode OTP yang valid.',
                ], 422);
            }

            return response()->json([
                'authenticated' => true,
                'required_method' => 'OTP',
                'message' => 'Otentikasi OTP berhasil (Lingkup IP Kantor).',
            ]);
        }

        $validKey = hash_equals($credential->secret_key, (string) ($validated['secret_key'] ?? ''));

        SecurityAuditLog::create([
            'employee_credential_id' => $credential->id,
            'nip' => $credential->nip,
            'ip_address' => $ip,
            'auth_method' => 'SECRET_KEY',
            'result' => $validKey ? 'ALLOWED' : 'BLOCKED',
            'description' => $validKey
                ? 'Verifikasi Secret Key berhasil dari IP Luar Kantor'
                : 'Percobaan akses luar kantor dengan Secret Key tidak cocok',
        ]);

        if (! $validKey) {
            return response()->json([
                'authenticated' => false,
                'required_method' => 'SECRET_KEY',
                'message' => 'IP terdeteksi di luar kantor. Wajib menyertakan Secret Key spesifik pegawai.',
            ], 403);
        }

        return response()->json([
            'authenticated' => true,
            'required_method' => 'SECRET_KEY',
            'message' => 'Otentikasi Secret Key berhasil (Akses Luar Kantor).',
        ]);
    }

    // Accepts either standard multipart upload or Base64 data URI from FileReader preview.
    private function persistPhoto(Request $request, ?string $existingPath = null): ?string
    {
        if ($request->hasFile('photo_file')) {
            if ($existingPath && Storage::disk('public')->exists($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }

            return $request->file('photo_file')->store('credentials', 'public');
        }

        $base64 = $request->input('photo_base64');
        if (is_string($base64) && str_starts_with($base64, 'data:image/')) {
            if (! preg_match('/^data:image\/(png|jpe?g|webp);base64,(.+)$/', $base64, $matches)) {
                return $existingPath;
            }

            $binary = base64_decode($matches[2], true);
            if ($binary === false || strlen($binary) > 2 * 1024 * 1024) {
                return $existingPath;
            }

            if ($existingPath && Storage::disk('public')->exists($existingPath)) {
                Storage::disk('public')->delete($existingPath);
            }

            $ext = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
            $filename = 'credentials/' . Str::uuid() . ".{$ext}";
            Storage::disk('public')->put($filename, $binary);

            return $filename;
        }

        return $existingPath;
    }
}