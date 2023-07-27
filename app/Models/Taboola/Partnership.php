<?php

namespace App\Models\Taboola;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Country;

class Partnership extends Model
{
    use HasFactory;

    protected $table = 'taboola_partnerships';

    protected $fillable = [
        'name',
    ];

    public function countries()
    {
        return $this->morphToMany(Country::class, 'countryables');
    }

    public function sites()
    {
        return $this->hasMany(Site::class);
    }
}
