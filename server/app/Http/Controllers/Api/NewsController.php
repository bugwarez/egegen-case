<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Requests\UpdateNewsRequest;
use App\Http\Resources\NewsResource;
use App\Models\News;
use App\Services\ImageProcessingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Haber API Controller'ı
 *
 * Bu controller haberlerin CRUD işlemlerini gerçekleştirir
 */
class NewsController extends Controller
{
    /**
     * Haber listesini getirir (pagination ile)
     *
     * Query parametreleri:
     * - search: Haber başlığı ve içeriğinde arama
     * - status: Haber durumuna göre filtreleme
     * - per_page: Sayfa başına kayıt sayısı (varsayılan: 15)
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = News::query();

        // Arama parametresi varsa filtrele
        if ($request->filled('search')) {
            $query->search($request->get('search'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $query->orderBy('created_at', 'desc');

        $perPage = min($request->get('per_page', 15), 100);
        $news = $query->paginate($perPage);

        return NewsResource::collection($news);
    }

    /**
     * Yeni haber oluşturur
     *
     * @param StoreNewsRequest $request
     * @param ImageProcessingService $imageService
     * @return JsonResponse
     */
    public function store(StoreNewsRequest $request, ImageProcessingService $imageService): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Görsel yükleme işlemi
            if ($request->hasFile('image')) {
                $imagePath = $imageService->processAndStore($request->file('image'));
                $validatedData['image_path'] = $imagePath;
            }

            $news = News::create($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Haber başarıyla oluşturuldu.',
                'data' => new NewsResource($news)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber oluşturulurken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Belirtilen haberi getirir
     *
     * @param News $news
     * @return JsonResponse
     */
    public function show(News $news): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new NewsResource($news)
        ]);
    }

    /**
     * Belirtilen haberi günceller
     *
     * @param UpdateNewsRequest $request
     * @param News $news
     * @param ImageProcessingService $imageService
     * @return JsonResponse
     */
    public function update(UpdateNewsRequest $request, News $news, ImageProcessingService $imageService): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            // Görsel güncelleme işlemi
            if ($request->hasFile('image')) {
                $imagePath = $imageService->processAndStore(
                    $request->file('image'),
                    $news->image_path // Eski görseli sil
                );
                $validatedData['image_path'] = $imagePath;
            }

            $news->update($validatedData);

            $news->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Haber başarıyla güncellendi.',
                'data' => new NewsResource($news)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber güncellenirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Belirtilen haberi siler
     *
     * @param News $news
     * @param ImageProcessingService $imageService
     * @return JsonResponse
     */
    public function destroy(News $news, ImageProcessingService $imageService): JsonResponse
    {
        try {
            // Haber görselini sil (eğer varsa)
            if ($news->image_path) {
                $imageService->deleteImage($news->image_path);
            }

            // Haberi veritabanından sil
            $news->delete();

            return response()->json([
                'success' => true,
                'message' => 'Haber başarıyla silindi.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Haber silinirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sadece yayınlanmış haberleri getirir
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
        public function published(Request $request): AnonymousResourceCollection
    {
        $query = News::published();

        if ($request->filled('search')) {
            $query->search($request->get('search'));
        }

        $query->orderBy('published_at', 'desc');

        $perPage = min($request->get('per_page', 15), 100);
        $news = $query->paginate($perPage);

        return NewsResource::collection($news);
    }

    /**
     * Haber slug'ına göre haber getirir
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function getBySlug(string $slug): JsonResponse
    {
        $news = News::where('slug', $slug)->first();

        if (!$news) {
            return response()->json([
                'success' => false,
                'message' => 'Haber bulunamadı.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new NewsResource($news)
        ]);
    }

    /**
     * Haberin durumunu değiştirir
     *
     * @param Request $request
     * @param News $news
     * @return JsonResponse
     */
    public function changeStatus(Request $request, News $news): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:draft,published,archived'
        ], [
            'status.required' => 'Durum alanı zorunludur.',
            'status.in' => 'Geçersiz durum. Seçenekler: draft, published, archived'
        ]);

        try {
            $news->update([
                'status' => $request->status,
                // Eğer published yapılıyorsa ve published_at boşsa şu anki zamanı set et
                'published_at' => $request->status === 'published' && !$news->published_at
                    ? now()
                    : $news->published_at
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Haber durumu başarıyla değiştirildi.',
                'data' => new NewsResource($news)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Durum değiştirilirken bir hata oluştu: ' . $e->getMessage()
            ], 500);
        }
    }
}
