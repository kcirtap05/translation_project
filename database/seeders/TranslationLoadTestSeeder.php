<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Locale;
use App\Models\Tag;
use App\Models\TranslationKey;
use App\Models\Translation;

class TranslationLoadTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Locale::factory()->count(3)->create();

        $tags = Tag::factory()->count(20)->create();

        $keys = TranslationKey::factory()->count(25000)->create();

        // Attach random tags to keys
        $keys->each(function ($key) use ($tags) {
            $key->tags()->attach(
                $tags->random(rand(1, 3))->pluck('id')->toArray()
            );
        });

        $locales = Locale::all();
        $translations = [];

        foreach ($keys as $key) {
            foreach ($locales as $locale) {
                $translations[] = [
                    'translation_key_id' => $key->id,
                    'locale_id' => $locale->id,
                    'content' => fake()->sentence(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (count($translations) >= 1000) {
                Translation::insert($translations);
                $translations = [];
            }
        }

        if (!empty($translations)) {
            Translation::insert($translations);
        }
    }
}
