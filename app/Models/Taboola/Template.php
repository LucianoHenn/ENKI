<?php

namespace App\Models\Taboola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;
use App\Models\Category;
use App\Models\Language;

class Template extends Model
{
    use HasFactory;

    protected $table = 'taboola_templates';

    protected $fillable = [
        'template',
        'description',
    ];

    protected $dates = [
        'created_at', 'updated_at'
    ];

    protected $casts = ['template' => 'array'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function countries()
    {
        return $this->morphToMany(Country::class, 'countryables');
    }
}
