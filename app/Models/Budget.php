<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;
    protected $fillable = ['date', 'budget_value', 'transactions_value', 'subcategory_id','user_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subcategory() 
    {
        return $this->belongsTo(Subcategory::class);
    }
}
