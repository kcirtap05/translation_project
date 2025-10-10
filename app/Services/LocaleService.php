<?php 

namespace App\Services;

use App\Http\Resources\LocaleResource;
use App\Models\Locale;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class LocaleService {
    private $model;

    public function __construct(Locale $model)
    {
        $this->model = $model;
    }

    public function grid($number_per_page) 
    {
        $data = $this->model->paginate($number_per_page); 
        $lists = Cache::remember('all_locales', now()->addMinutes(10), function () use ($data) {
            return $data; 
        });
        return LocaleResource::collection($lists);
    }

    public function create(array $data) 
    {
        try {

            DB::beginTransaction();
            $this->model->create($data);
            DB::commit();

            Cache::forget('all_locales'); 

            return new LocaleResource($data);

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

            Cache::forget('all_locales'); 

            return new LocaleResource($task_data);
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

            Cache::forget('all_locales'); 

            return new LocaleResource($data);

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