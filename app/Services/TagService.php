<?php 

namespace App\Services;

use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

class TagService {
    private $model;

    public function __construct(Tag $model)
    {
        $this->model = $model;
    }

    public function grid($number_per_page) 
    {
        $data = $this->model->paginate($number_per_page); 
        $lists = Cache::remember('all_tags', now()->addMinutes(10), function () use ($data) {
            return $data; 
        });
        return TagResource::collection($lists);
    }

    public function create(array $data) 
    {
        try {

            DB::beginTransaction();
            $this->model->create($data);
            DB::commit();

            Cache::forget('all_tags'); 

            return new TagResource($data);

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

            Cache::forget('all_tags'); 

            return new TagResource($task_data);
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

            Cache::forget('all_tags'); 

            return new TagResource($data);

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