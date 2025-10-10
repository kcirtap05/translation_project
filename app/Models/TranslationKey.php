<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TranslationKey extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'description'];

    public function translations()
    {
        return $this->hasMany(Translation::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'translation_key_tag');
    }
}
