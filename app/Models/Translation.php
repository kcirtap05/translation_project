<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Translation extends Model
{
    //
    use HasFactory;

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['id', 'translation_key_id', 'locale_id', 'content'];
    
    public function key()
    {
        return $this->belongsTo(TranslationKey::class, 'translation_key_id');
    }

    public function locale()
    {
        return $this->belongsTo(Locale::class, 'locale_id');
    }
}
