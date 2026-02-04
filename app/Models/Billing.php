<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Billing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'code',
        'amount',
        'year',
        'company_id',
        'status',
        'is_registered',
        'providence',
        'type',
        'payment_state',
        'edited_at',
        'last_paid_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'year' => 'integer',
            'is_registered' => 'boolean',
            'edited_at' => 'datetime',
            'last_paid_at' => 'datetime',
        ];
    }

    /**
     * Get the company that owns the billing.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
