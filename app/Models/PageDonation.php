<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageDonation extends Model
{
    public const TYPE_TRANSFER = 'transfer';

    public const TYPE_DATANG_LANGSUNG = 'datang_langsung';

    protected $fillable = [
        'page_id',
        'gift_type',
        'bank_name',
        'account_name',
        'account_number',
        'address',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function isTransfer(): bool
    {
        return ($this->gift_type ?? self::TYPE_TRANSFER) === self::TYPE_TRANSFER;
    }

    public function isDirect(): bool
    {
        return $this->gift_type === self::TYPE_DATANG_LANGSUNG;
    }
}
