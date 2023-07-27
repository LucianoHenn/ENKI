<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'original_image_id',
        'image_name',
        'url',
        'hash',
        'perceptual_hash',
        'width',
        'height',
        'size',
        'mimetype',
        'info',
        'path'
    ];

    public function originalImage()
    {
        return $this->belongsTo(Image::class, 'original_image_id');
    }

    public function duplicateImages()
    {
        return $this->hasMany(Image::class, 'original_image_id');
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function keywords()
    {
        return $this->morphToMany(Keyword::class, 'keywordable');
    }

    public function getTemporaryUrlAttribute()
    {

        return Storage::disk('s3')->temporaryUrl($this->url, now()->addMinutes(10));
    }

    public static function getTemporaryUrl($path, $duration)
    {
        return Storage::disk('s3')->temporaryUrl($path, $duration);
    }
}
