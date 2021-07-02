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

    public function balance() {
        $balance = 0;
        $credits = $this->hasMany(Transaction::class, 'credit_account_id', 'id')->where('deleted_at', null)->get()->toArray();
        $debits = $this->hasMany(Transaction::class, 'debit_account_id', 'id')->where('deleted_at', null)->get()->toArray();
        foreach ($credits as $key => $value) {
            $balance -= $value['amount'];
        }
        foreach ($debits as $key => $value) {
            $balance += $value['amount'];
        }
        return $balance;
    }

    public function lastTransactionDate() {
        $credits = $this->hasMany(Transaction::class, 'credit_account_id', 'id')->where('deleted_at', null)->with('transactionsJournal')->get()->toArray();
        $debits = $this->hasMany(Transaction::class, 'debit_account_id', 'id')->where('deleted_at', null)->with('transactionsJournal')->get()->toArray();
        $creditDates = array_column(array_column($credits,'transactions_journal'),'date');
        $debitDates = array_column(array_column($debits,'transactions_journal'),'date');
        $dates = array_merge($creditDates, $debitDates);
        if (count($dates) > 0)
            return max($dates);
        else
            return "";
    }


}


