<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Account extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description', 'currency_id', 'number','role','bank_id','user_id', 'statementClosingDay','statementDueDay'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}


