<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
    ];

    // See Product::$hidden — same Oracle ROWNUM artifact shows up once a
    // query combines an aggregate (withCount) with pagination.
    protected $hidden = ['rn'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
