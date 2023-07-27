<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Google\Cloud\Translate\V2\TranslateClient;

class Keyword extends Model
{
    use HasFactory;

    protected $fillable = [
        'keyword',
        'english_translation',
        'country_id',
        'language_id',
        'category_id'
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function translate($target = 'en')
    {
        $translateClient = new TranslateClient(['key' => config('services.google_translate.key')]);
        $translated = $translateClient->translate($this->keyword, ['target' => $target]);
        return $this->english_translation = htmlspecialchars_decode($translated['text']);
    }

    public function images()
    {
        return $this->morphedByMany(Image::class, 'keywordable')->orderByPivot('id', 'desc');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
