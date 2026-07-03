<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GoodsReceipt extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'receipt_code',
        'supplier_id',
        'created_by',
        'total_amount',
        // $timestamps is false (no updated_at column) so this must be mass
        // assignable — it's set explicitly on create() instead of relying on
        // Eloquent's automatic timestamp management.
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'created_at' => 'datetime',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(GoodsReceiptDetail::class, 'receipt_id');
    }
}
