<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_id',
        'name',
        'description',
        'price',
        'image',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
