<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    //
    use HasFactory;

    protected $fillable = ['name', 'description'];

    public function translationKeys()
    {
        return $this->belongsToMany(TranslationKey::class, 'translation_key_tag');
    }
}
