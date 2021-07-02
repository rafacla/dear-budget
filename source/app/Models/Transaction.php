<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'deleted_at', 'reconciled', 'credit_account_id', 'debit_account_id', 'type', 'transactions_journal_id', 'amount', 
        'foreign_amount', 'foreign_currency_id', 'subcategory_id'
    ];

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
