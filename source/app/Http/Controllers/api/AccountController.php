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
    /**
     * @OA\Get(
     *      path="/accounts",
     *      tags={"Accounts"},
     *      summary="Get list of user accounts",
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function index()
    {
        $transactionTypes = config('dearbudget.transactionTypes');
        $accounts = Account::where('user_id', Auth::user()->id)->get();
        foreach ($accounts as $key => $value) {
            $transaction = Transaction::where('type', array_search('initialBalance', array_column($transactionTypes, 'type')))
                ->where('debit_account_id', $value->id)->get()->first();
            $accounts[$key]['initialBalanceAmount'] = $transaction->amount ?? 0;
            $accounts[$key]['initialBalanceDate'] = $transaction->transactionsJournal->date ?? 'not found';
        }
        return $accounts;
    }

    /**
     * @OA\Post(
     *      path="/accounts",
     *      tags={"Accounts"},
     *      summary="Create a new account",
     *      security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(
     *                  property="role",
     *                  type="string",
     *                  enum={"checkingAccount", "walletCash", "investmentAccount", "creditCard", "expenseAccount", "incomeAccount"},
     *              ),
     *              @OA\Property(property="initialBalanceDate", type="date"),
     *              @OA\Property(property="initialBalanceAmount", type="number"),
     *              @OA\Property(property="statementClosingDay", type="number"),
     *              @OA\Property(property="statementDueDay", type="number"),
     *              required={"name", "role"}
     *           ),
     *       )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function store(Request $request)
    {
        $accountRoles = config('dearbudget.accountRoles');
        $transactionTypes = config('dearbudget.transactionTypes');

        $validation = Validator::make($request->all(), [
            'name' => 'required',
            'role' => 'required|in:' . implode(",", array_keys($accountRoles)),
            'statementClosingDay' => 'required_if:role,creditCard',
            'statementDueDay' => 'required_if:role,creditCard',
        ]);
        if ($validation->fails()) {
            return response('Missing parameters: ' . $validation->errors());
        } else {
            if ($request['role'] == null || ($request['role'] != null && array_key_exists($request['role'], $accountRoles) == false)) {
                return response('Invalid Account role', 403);
            } else {
                $request['user_id'] = Auth::user()->id;
                $account = Account::create($request->all());
                if ($account) {
                    if ($request['initialBalanceDate'] == null) {
                        $request['initialBalanceDate'] = (new DateTime())->format('Y-m-d');
                    } else {
                        $date = DateTime::createFromFormat('Y-m-d', $request['initialBalanceDate']);
                        if ($date == null) {
                            $request['initialBalanceDate'] = (new DateTime())->format('Y-m-d');
                        } else {
                            $request['initialBalanceDate'] = $date->format('Y-m-d');
                        }
                    }
                    if ($request['initialBalanceAmount'] == null) {
                        $request['initialBalanceAmount'] = 0;
                    }
                    $transactionsJournal = TransactionsJournal::create(
                        [
                            'user_id' => $request['user_id'],
                            'date' => $request['initialBalanceDate'],
                            'description' => __('Opening Balance')
                        ]
                    );
                    $transaction = Transaction::create([
                        'debit_account_id' => $account->id,
                        'type' => array_search('initialBalance', array_column($transactionTypes, 'type')),
                        'transactions_journal_id' => $transactionsJournal->id,
                        'amount' => $request['initialBalanceAmount'],
                    ]);
                    return response('Account created.', 201);
                } else
                    return response('Failed', 500);
            }
        }
    }

    /**
     * @OA\Get(
     *      path="/accounts/{accountID}",
     *      tags={"Accounts"},
     *      summary="Get a specific account",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="accountID",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function show($id)
    {
        $transactionTypes = config('dearbudget.transactionTypes');
        $account = Account::findOrFail($id);
        $transaction = Transaction::where('type', array_search('initialBalance', array_column($transactionTypes, 'type')))
            ->where('debit_account_id', $account->id)->get()->first();
        $account['initialBalanceAmount'] = $transaction->amount;
        $account['initialBalanceDate'] = $transaction->transactionsJournal->date;
        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            return $account;
    }

    /**
     * @OA\Put(
     *      path="/accounts/{accountID}",
     *      tags={"Accounts"},
     *      summary="Update an account",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="accountID",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *              @OA\Property(property="name", type="string"),
     *              @OA\Property(
     *                  property="role",
     *                  type="string",
     *                  enum={"checkingAccount", "walletCash", "investmentAccount", "creditCard", "expenseAccount", "incomeAccount"},
     *              ),
     *              @OA\Property(property="initialBalanceDate", type="date"),
     *              @OA\Property(property="initialBalanceAmount", type="number"),
     *              @OA\Property(property="statementClosingDay", type="number"),
     *              @OA\Property(property="statementDueDay", type="number"),
     *           ),
     *       )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else {
            $transactionTypes = config('dearbudget.transactionTypes');
            $accountRoles = config('dearbudget.accountRoles');
            $transaction = Transaction::where('type', array_search('initialBalance', array_column($transactionTypes, 'type')))
                ->where('debit_account_id', $account->id)->first();
            $transactionJournal = TransactionsJournal::where('id', $transaction['transactions_journal_id'])->get();
            if (($request['role'] != null && array_key_exists($request['role'], $accountRoles) == false)) {
                return response('Invalid Account role', 403);
            } else {
                if ($account->update($request->all())) {

                    if ($request['initialBalanceAmount'] != null) {
                        $transaction->update(['amount' => $request['initialBalanceAmount']]);
                    }
                    if ($request['initialBalanceDate'] != null) {
                        $transactionJournal->update(['date' => $request['initialBalanceDate']]);
                    }
                    return response('Account updated.', 200);
                } else
                    return response('Failed', 500);
            }
        }
    }

    /**
     * @OA\Delete(
     *      path="/accounts/{accountID}",
     *      tags={"Accounts"},
     *      summary="Delete a specific account",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="accountID",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *     )
     */
    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            $account->delete();
    }
}
