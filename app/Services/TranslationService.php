<?php 

namespace App\Services;

use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\Locale;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class TranslationService {
    private $model;

    public function __construct(Translation $model)
    {
        $this->model = $model;
    }

    

    public function grid($number_per_page) 
    {
        $start = microtime(true);
        $data = $this->model->paginate($number_per_page); 
        $lists = Cache::remember('all_translations', now()->addMinutes(10), function () use ($data) {
            return $data; 
        });

        return response()->json([
            'success' => true,
            'message' => "Found {$lists->total()} translations in total",
            'data' => TranslationResource::collection($lists),
            'meta' => [
                'current_page' => $lists->currentPage(),
                'per_page' => $lists->perPage(),
                'total' => $lists->total(),
                'last_page' => $lists->lastPage(),
                'latency' => microtime(true) - $start
            ],
        ]);
    }

    public function create(array $data) 
    {
        try {
            DB::beginTransaction();


            $translationKey = TranslationKey::create([
                'key' => $data['key'],
                'desciption' => $data['desciption'] ?? [],
            ]);

            $tagIds = collect($data['tags'] ?? [])->map(function ($tagName) {
                return Tag::firstOrCreate(['name' => $tagName])->id;
            })->filter()->toArray();
        
            $translationKey->tags()->sync($tagIds);

    
            foreach ($data['translations'] as $localeCode => $content) {
                $locale = Locale::firstOrCreate(['code' => $localeCode], ['name' => ucfirst($localeCode)]);

                Translation::create([
                    'translation_key_id' => $translationKey->id,
                    'locale_id' => $locale->id,
                    'content' => $content,
                ]);
            }
    
            DB::commit();

            Cache::forget('all_translations'); 

            return new TranslationResource($data);

        } catch (Throwable $e) {
            DB::rollback();
            return response()->json([
                'code'   => 500,
                'status'  => 'fail',
                'message' => 'UNHANDLED EXCEPTION',
                'data'    => $e->getMessage(),
            ]);
        }
    }

    public function update($id,$data) 
    {
        try {

            DB::beginTransaction();
            $task_data = tap($this->model->find($id))->update($data);
            DB::commit();

            Cache::forget('all_translations'); 

            return new TranslationResource($task_data);
        } catch (Throwable $e) {
            DB::rollback();
            return response()->json([
                'code'   => 500,
                'status'  => 'fail',
                'message' => 'UNHANDLED EXCEPTION',
                'data'    => $e->getMessage(),
            ]);
        }
    }

    public function delete($id) 
    {
        try {

            DB::beginTransaction();
            $data = tap($this->model->find($id))->delete();
            DB::commit();

            Cache::forget('all_translations'); 

            return new TranslationResource($data);

        } catch (Throwable $e) {
            DB::rollback();
            return response()->json([
                'code'   => 500,
                'status'  => 'fail',
                'message' => 'UNHANDLED EXCEPTION',
                'data'    => $e->getMessage(),
            ]);
        }
        
    }

    public function searchTranslations(array $filters)
    {
        $start = microtime(true);
        $query = TranslationKey::with(['translations.locale', 'tags']);

        if (!empty($filters['key'])) {
            $query->where('key', 'like', '%' . $filters['key'] . '%');
        }

        if (!empty($filters['content'])) {
            $query->whereHas('translations', function ($q) use ($filters) {
                $q->whereFullText('content', $filters['content']);
            });
        }

        if (!empty($filters['tags'])) {
            $tags = is_array($filters['tags']) ? $filters['tags'] : [$filters['tags']];
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        if (!empty($filters['locale'])) {
            $query->whereHas('translations.locale', function ($q) use ($filters) {
                $q->where('code', $filters['locale']);
            });
        }

        if (isset($filters['has_description'])) {
            if ($filters['has_description']) {
                $query->whereNotNull('description');
            } else {
                $query->whereNull('description');
            }
        }

        // Date range filters
        if (!empty($filters['created_after'])) {
            $query->where('created_at', '>=', $filters['created_after']);
        }

        if (!empty($filters['created_before'])) {
            $query->where('created_at', '<=', $filters['created_before']);
        }

        if (!empty($filters['updated_after'])) {
            $query->where('updated_at', '>=', $filters['updated_after']);
        }

        if (!empty($filters['updated_before'])) {
            $query->where('updated_at', '<=', $filters['updated_before']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        $translations = $query->paginate($filters['per_page'] ?? 15);

        return response()->json([
            'success' => true,
            'message' => "Found {$translations->total()} translations matching key pattern",
            'data' => TranslationResource::collection($translations),
            'meta' => [
                'current_page' => $translations->currentPage(),
                'per_page' => $translations->perPage(),
                'total' => $translations->total(),
                'last_page' => $translations->lastPage(),
                'latency' => microtime(true) - $start
            ],
        ]);
    }

    public function searchByTags(array $tags, string $match = 'any')
    {
        $start = microtime(true);
        $query = TranslationKey::with(['translations.locale', 'tags']);

        if ($match === 'all') {
            foreach ($tags as $tag) {
                $query->whereHas('tags', function ($q) use ($tag) {
                    $q->where('name', $tag);
                });
            }
        } else {
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('name', $tags);
            });
        }

        $translations =  $query->paginate(15);
        
        return response()->json([
            'success' => true,
            'message' => "Found {$translations->total()} translations with specified tags",
            'data' => TranslationResource::collection($translations),
            'meta' => [
                'current_page' => $translations->currentPage(),
                'per_page' => $translations->perPage(),
                'total' => $translations->total(),
                'last_page' => $translations->lastPage(),
                'latency' => microtime(true) - $start
            ],
        ]);
    }
}