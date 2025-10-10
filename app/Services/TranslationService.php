<?php 

namespace App\Services;

use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\Locale;
use App\Models\Tag;
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
        $data = $this->model->paginate($number_per_page); 
        $lists = Cache::remember('all_translations', now()->addMinutes(10), function () use ($data) {
            return $data; 
        });

        return TranslationResource::collection($lists);
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
}