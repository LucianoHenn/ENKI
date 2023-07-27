<?php

namespace App\Models\Taboola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;

class Domain extends Model
{
    use HasFactory;

    protected $table = 'taboola_domains';

    protected $fillable = [
        'name',
        'domain',
        'status',
        'partnership_id',
    ];

    protected $casts = ['domain' => 'array'];

    public function countries()
    {
        return $this->morphToMany(Country::class, 'countryables');
    }

    public function partnership()
    {
        return $this->belongsTo(Partnership::class);
    }
}
