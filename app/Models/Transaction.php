<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public function transactionsJournal()
    {
        return $this->belongsTo(TransactionsJournal::class);
    }

    public function creditAccount() {
        return $this->hasOne(Account::class, 'id','credit_account_id');
    }

    public function debitAccount() {
        return $this->hasOne(Account::class, 'id','debit_account_id');
    }

    public function subcategory() {
        return $this->hasOne(Subcategory::class, 'id','subcategory_id');
    }
}
