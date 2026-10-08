<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCameraRequest;
use App\Http\Resources\CameraResource;
use App\Models\Camera;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CameraController extends Controller
{
    /**
     * Menampilkan daftar katalog kamera untuk publik.
     * Mendukung pencarian (search), filter status/brand, dan paginasi.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Camera::query();

        // Filter berdasarkan pencarian nama atau brand
        if ($request->filled('search')) {
            $search =$request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        // Filter berdasarkan brand spesifik
        if ($request->filled('brand')) {
            $query->where('brand',$request->brand);
        }

        // Filter berdasarkan status (default: tampilkan semua atau filter spesifik)
        if ($request->filled('status')) {
            $query->where('status',$request->status);
        }

        // Pengurutan (sorting)
        $sortBy =$request->get('sort_by', 'created_at');
        $sortOrder =$request->get('sort_order', 'desc');

        if (in_array($sortBy, ['daily_rate', 'name', 'created_at'])) {$query->orderBy($sortBy,$sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->latest();
        }

        $cameras = $query->paginate($request->get('per_page', 12));

        return CameraResource::collection($cameras);

    }

    /**
     * Menampilkan detail informasi unit kamera tunggal.
     */
    public function show($id): CameraResource|JsonResponse
    {
        $camera = Camera::find($id);

        if (!$camera) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kamera tidak ditemukan.',
            ], 404);
        }

        return new CameraResource($camera);
    }


    public function store(StoreCameraRequest $request): JsonResponse
    {
        $validated =$request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] =$request->file('image')->store('cameras', 'public');
        }

        $camera = Camera::create($validated);

        return (new CameraResource($camera))
            ->additional(['message' => 'Kamera berhasil ditambahkan.'])
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Memperbarui data kamera & mengganti foto jika ada foto baru [Khusus Admin].
     * Catatan: Karena form-data HTML dengan method PUT/PATCH sering mengalami kendala parsing di PHP,
     * request dapat dikirim via POST dengan parameter `_method=PUT`.
     */
    public function update(StoreCameraRequest $request,$id): JsonResponse
    {
        $camera = Camera::find($id);

        if (!$camera) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kamera tidak ditemukan.',
            ], 404);
        }

        $validated =$request->validated();

        // Jika ada unggahan gambar baru
        if ($request->hasFile('image')) {
            // Hapus gambar lama dari storage jika file lama ada
            if ($camera->image && Storage::disk('public')->exists($camera->image)) {
                Storage::disk('public')->delete($camera->image);
            }

            // Simpan gambar baru
            $validated['image'] =$request->file('image')->store('cameras', 'public');
        }

        $camera->update($validated);

        return (new CameraResource($camera))
            ->additional(['message' => 'Data kamera berhasil diperbarui.'])
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Menghapus unit kamera & menghapus file foto dari storage [Khusus Admin].
     */
    public function destroy($id): JsonResponse
    {
        $camera = Camera::find($id);

        if (!$camera) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Kamera tidak ditemukan.',
            ], 404);
        }

        // Hapus file gambar dari disk public jika ada
        if ($camera->image && Storage::disk('public')->exists($camera->image)) {
            Storage::disk('public')->delete($camera->image);
        }

        $camera->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Kamera berhasil dihapus.',
        ], 200);
    }
}