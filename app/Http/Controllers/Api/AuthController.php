<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Registrasi pengguna baru (Customer) beserta unggah foto KTP.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'ktp_image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
        ]);

        // Simpan file KTP ke storage/app/public/ktp_files
        $ktpPath = null;
        if ($request->hasFile('ktp_image')) {
            $ktpPath = $request->file('ktp_image')->store('ktp_files', 'public');
        }

        $user = User::create([
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'role'      => 'customer',
            'ktp_image' => $ktpPath,
            'status'    => 'pending',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Registrasi berhasil, akun Anda sedang menunggu verifikasi admin',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'role'      => $user->role,
                'status'    => $user->status,
                'ktp_image' => $user->ktp_image ? asset('storage/' . $user->ktp_image) : null,
            ],
        ], 201);
    }

    /**
     * Autentikasi user / admin & penerbitan Bearer Token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
        }

        // Cek status persetujuan jika role pengguna adalah customer
        if ($user->role === 'customer' && $user->status !== 'approved') {
            $message = $user->status === 'rejected' 
                ? 'Akun Anda ditolak oleh admin.' 
                : 'Akun Anda masih menunggu verifikasi admin.';

            throw ValidationException::withMessages([
                'email' => [$message],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login berhasil',
            'token'        => $token,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => [
                'id'        => $user->id,
                'name'      => $user->name,
                'email'     => $user->email,
                'phone'     => $user->phone,
                'role'      => $user->role,
                'status'    => $user->status,
                'ktp_image' => $user->ktp_image ? asset('storage/' . $user->ktp_image) : null,
            ],
        ], 200);
    }

    /**
     * Mengambil informasi profil pengguna yang sedang login.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'user' => [
                'id'         => $user->id,
                'name'       => $user->name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'role'       => $user->role,
                'status'     => $user->status,
                'ktp_image'  => $user->ktp_image ? asset('storage/' . $user->ktp_image) : null,
                'created_at' => $user->created_at,
            ],
        ], 200);
    }

    /**
     * Logout pengguna dan mencabut token aktif.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil',
        ], 200);
    }

    /**
     * [ADMIN] Menampilkan daftar pengguna yang statusnya masih 'pending' (perlu verifikasi KTP).
     */
    public function pendingUsers(): JsonResponse
    {
        $pendingUsers = User::where('role', 'customer')
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(function ($user) {
                return [
                    'id'        => $user->id,
                    'name'      => $user->name,
                    'email'     => $user->email,
                    'phone'     => $user->phone,
                    'status'    => $user->status,
                    'ktp_image' => $user->ktp_image ? asset('storage/' . $user->ktp_image) : null,
                    'created_at' => $user->created_at,
                ];
            });

        return response()->json([
            'data' => $pendingUsers
        ], 200);
    }

    /**
     * [ADMIN] Menyetujui (ACC) pendaftaran akun pengguna.
     */
    public function approve(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'approved']);

        return response()->json([
            'message' => 'Akun pengguna berhasil disetujui (ACC)',
            'user'    => $user
        ], 200);
    }

    /**
     * [ADMIN] Menolak pendaftaran akun pengguna.
     */
    public function reject(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'rejected']);

        return response()->json([
            'message' => 'Akun pengguna telah ditolak',
            'user'    => $user
        ], 200);
    }
}