<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\TransactionsJournal;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class AccountController extends Controller
{
    public function index()
    {
        $transactionTypes = config('dearbudget.transactionTypes');
        $accounts = Account::where('user_id',Auth::user()->id)->get();
        foreach ($accounts as $key => $value) {
            $transaction = Transaction::where('type',array_search('initialBalance',array_column($transactionTypes,'type')))
                ->where('debit_account_id',$value->id)->get()->first();
            $accounts[$key]['initial_balance_amount'] = $transaction->amount;
            $accounts[$key]['initial_balance_date'] = $transaction->transactionsJournal->date;
        }
        return $accounts;
    }

    public function store(Request $request)
    {
        $accountRoles = config('dearbudget.accountRoles');
        $transactionTypes = config('dearbudget.transactionTypes');

        $validation = Validator::make($request->all(),[
            'name' => 'required',
            'role' => 'required|in:'.implode(",",array_keys($accountRoles)),
            'statementClosingDay' => 'required_if:role,creditCard',
            'statementDueDay' => 'required_if:role,creditCard',
        ]);
        if ($validation->fails()) {
            return response('Missing parameters: '.$validation->errors());
        } else {
            if ($request['role'] == null || ($request['role'] != null && array_key_exists($request['role'],$accountRoles) == false)) {
                return response('Invalid Account role', 403);
            } else {
                $request['user_id'] = Auth::user()->id;
                $account = Account::create($request->all());
                if ($account) {
                    if ($request['initial_balance_date'] == null) {
                        $request['initial_balance_date'] = (new DateTime())->format('Y-m-d');
                    } else {
                        $date = DateTime::createFromFormat('Y-m-d', $request['initial_balance_date']);
                        if ($date == null) {
                            $request['initial_balance_date'] = (new DateTime())->format('Y-m-d');
                        } else {
                            $request['initial_balance_date'] = $date->format('Y-m-d');
                        }
                    }
                    if ($request['initial_balance_amount'] == null) {
                        $request['initial_balance_amount'] = 0;
                    }
                    $transactionsJournal = TransactionsJournal::create(
                        [
                            'user_id' => $request['user_id'],
                            'date' => $request['initial_balance_date'],
                            'description' => __('Opening Balance')
                        ]
                    );
                    $transaction = Transaction::create([
                        'debit_account_id' => $account->id,
                        'type' => array_search('initialBalance',array_column($transactionTypes,'type')),
                        'transactions_journal_id' => $transactionsJournal->id,
                        'amount' => $request['initial_balance_amount'],
                    ]);
                    return response('Account created.', 201);
                } else
                    return response('Failed', 500);
            }
        }
    }

    public function show($id)
    {
        $transactionTypes = config('dearbudget.transactionTypes');
        $account = Account::findOrFail($id);
        $transaction = Transaction::where('type',array_search('initialBalance',array_column($transactionTypes,'type')))
            ->where('debit_account_id',$account->id)->get()->first();
        $account['initial_balance_amount'] = $transaction->amount;
        $account['initial_balance_date'] = $transaction->transactionsJournal->date;
        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            return $account;
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else {
            $transactionTypes = config('dearbudget.transactionTypes');
            $accountRoles = config('dearbudget.accountRoles');
            $transaction = Transaction::where('type',array_search('initialBalance',array_column($transactionTypes,'type')))
                ->where('debit_account_id',$account->id)->first();
            $transactionJournal = TransactionsJournal::where('id',$transaction['transactions_journal_id'])->get();
            if ($request['role'] == null || ($request['role'] != null && array_key_exists($request['role'],$accountRoles) == false)) {
                return response('Invalid Account role', 403);
            } else {
                if ($account->update($request->all())) {
                    if ($request['initial_balance_amount'] != null) {
                        $transaction->update(['amount' => $request['initial_balance_amount']]);
                    }
                    if ($request['initial_balance_date'] != null) {
                        $transactionJournal->update(['date' => $request['initial_balance_date']]);
                    }
                    return response('Account updated.', 200);
                } else
                    return response('Failed', 500);
            }
        }
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            $account->delete();
    }
}
