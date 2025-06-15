<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'user_id',
        'client_name',
        'client_whatsapp',
        'client_email',
        'client_address',
        'title',
        'description',
        'date',
        'total',
        'payment_methods',
        'discounts',
        'installments',
        'total_with_discount',
        'status',
        'pdf_path',
        'valid_until',
        'delivery_time',
    ];


    protected $casts = [
        'payment_methods' => 'array',
        'discounts' => 'array',
        'date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(BudgetItem::class);
    }
}
