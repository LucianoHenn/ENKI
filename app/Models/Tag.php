<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'value',
    ];

    public function images()
    {
        return $this->morphedByMany(Image::class, 'taggable');
    }

    public function keywords()
    {
        return $this->morphedByMany(Keyword::class, 'taggable');
    }
}
