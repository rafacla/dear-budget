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

    public function creditAccounts() {
        return $this->hasMany(Account::class, 'credit_account_id');
    }

    public function debitAccounts() {
        return $this->hasMany(Account::class, 'debit_account_id');
    }
}
