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

class TranslationService extends BaseService {
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

        return $this->apiResponse(200, "Found {$lists->total()} translations in total", $lists, 200, '', '', $start);
    }

    public function create(array $data) 
    {
        return $this->executeFunction(function() use ($data) {

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

            Cache::forget('all_translations'); 

            return new TranslationResource($data);
        });
    }

    public function update($id,$data) 
    {
        return $this->executeFunction(function() use ($id, $data) {

            $task_data = tap($this->model->find($id))->update($data);

            Cache::forget('all_translations'); 

            return new TranslationResource($task_data);
        });
    }

    public function delete($id) 
    {
        return $this->executeFunction(function() use ($id) {

            $data = tap($this->model->find($id))->delete();

            Cache::forget('all_translations'); 

            return new TranslationResource($data);
        });
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

        $data = TranslationResource::collection($translations);

        return $this->apiResponse(200, "Successful", $data, 200, '', '', $start);

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

        $data = TranslationResource::collection($translations);

        return $this->apiResponse(200, "Found {$translations->total()} translations with specified tags", $data, 200, '', '', $start);
    }

    public function advancedSearch($filters)
    {
        return $this->searchTranslations($filters);
    }

    public function binarySearch($arr, $low, $high, $x) {
        // x = value to be search
        // low = starting index
        // high = ending index
        // arr = array to be search
        while ($low <= $high) {

            $mid = ceil($low + ($high - $low) / 2);

            if ($arr[$mid] == $x) {
                return floor($mid); // x 
            }

            if ($arr[$mid] < $x) {
                $low = $mid + 1; // x is in right half
            } else {
                $high = $mid - 1; // x is in left half
            }
        }

        return -1; // x is not present in array
    }

    public function linearSearch($arr, $x) {
        $n = count($arr);
        for ($i = 0; $i < $n; $i++) {
            if ($arr[$i] == $x) {
                return $i; // x found at index i
            }
        }
        return -1; // x not found
    }

    public function getX($data) {
        $arr = TranslationKey::orderBy('id','asc')->pluck('id')->toArray();
        // $arr = array(2, 3, 4, 10, 40);
        // return $arr;
        $low = 0;
        $high = count($arr) - 1;
        $x = $data->input('x'); 

        // $result = $this->linearSearch($arr, $x);
        $result = $this->binarySearch($arr, $low, $high, $x);

        if(($result == -1)) {
            return "Element is not present in array";
        }
        else {
            return "Element is present at index ".$result;
        }
    }
}