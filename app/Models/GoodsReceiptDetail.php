<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GoodsReceiptDetail extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'receipt_id',
        'product_variant_id',
        'quantity',
        'import_price',
        'total_price',
    ];

    protected function casts(): array
    {
        return [
            'import_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(GoodsReceipt::class, 'receipt_id');
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
