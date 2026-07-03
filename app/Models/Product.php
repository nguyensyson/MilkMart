<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Product extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'description',
    ];

    // 'rn' is a ROWNUM artifact the Oracle grammar injects on any query
    // paginated/limited by this driver (yajra/laravel-oci8); hide it so it
    // never leaks into API/Inertia responses.
    protected $hidden = ['rn'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Only "status = active" variants are sellable/visible to customers.
     * A product with none of these is treated as hidden/discontinued.
     */
    public function activeVariants(): HasMany
    {
        return $this->variants()->where('status', 'active');
    }

    public function cheapestVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->ofMany(['price' => 'min'], function ($query) {
            $query->where('status', 'active');
        });
    }

    /**
     * One representative thumbnail for admin listings — the first variant
     * image flagged as primary, regardless of which variant it belongs to.
     */
    public function primaryImage(): HasOneThrough
    {
        return $this->hasOneThrough(
            ProductImage::class,
            ProductVariant::class,
            'product_id',
            'product_variant_id',
            'id',
            'id',
        )->where('is_primary', true);
    }
}
