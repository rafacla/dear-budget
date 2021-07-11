<?php

namespace App\Http\Controllers\api;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use App\Models\TransactionsJournal;
use App\Models\Transaction;
use App\Models\Account;
use App\Models\Currency;

class TransactionController extends Controller
{
    public $itemClass = TransactionsJournal::class;

    /**
     * @OA\Get(
     *      path="/transactions",
     *      tags={"Transactions"},
     *      summary="Get list of transactions",
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
        $items = $this->itemClass::where('user_id', Auth::user()->id)->with('transactions')->get();
        return $items;
    }

    /**
     * @OA\Post(
     *      path="/transactions",
     *      tags={"Transactions"},
     *      summary="Create a new transaction",
     *      security={{"bearer_token":{}}},
     *     @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="multipart/form-data",
     *           @OA\Schema(
     *              @OA\Property(property="date", type="date"),
     *              @OA\Property(property="budget_date", type="date"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="transaction_number", type="string"),
     *              @OA\Property(
     *                  property="transactions",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="credit_account_name",
     *                          description="Name of an existing account or a new income account one",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="debit_account_name",
     *                          description="Name of and existing account or a new debit account one",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="amount",
     *                          description="Value of Transaction",
     *                          type="number"
     *                      ),
     *                      @OA\Property(
     *                          property="subcategory_name",
     *                          description="Name of subcategory, if it doesn't exist or if this is a transfer, it'll be created without one",
     *                          type="string"
     *                      )
     *                  )
     *              ),
     *              required={"date", "description"}
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
        $transactionJournal = $request->all();
        //Quick and dirty fix for Swagger (adding items to array):
        if (is_string($transactionJournal['transactions'])) {
            if (substr($transactionJournal['transactions'], 0, 1) != '[') {
                $transactionJournal['transactions'] = '[' . $transactionJournal['transactions'] . ']';
            }
            $transactionJournal['transactions'] = json_decode($transactionJournal['transactions'], true);
        }
        $validation = Validator::make($transactionJournal, [
            'date' => 'required',
            'description' => 'required',
            'transactions.0.credit_account_name' => 'required',
            'transactions.0.debit_account_name' => 'required',
            'transactions.0.amount' => 'required|numeric'
        ]);

        if ($validation->fails()) {
            return response('Missing parameters: ' . $validation->errors());
        } else {
            $request['user_id'] = Auth::user()->id;
            if ($request['transaction_number'] != null && ($request['filter_duplicated'] == null || $request['filter_duplicated'] == 'no')) {
                $items = TransactionsJournal::where('transaction_number', $request['transaction_number'])->get();
            } elseif ($request['filter_duplicated'] == null || $request['filter_duplicated'] == 'no' ) {
                $items = $this->itemClass::where('date', $request['date'])
                ->where('description', $request['description'])->get();
                $amountRequest = 0;
                foreach ($request['transactions'] as $value) {
                    $amountRequest += $value['amount'];
                }
                foreach ($items as $key => $value) {
                    $amount = 0;
                    foreach ($value->transactions as $transactionValue) {
                        $amount += $transactionValue->amount;
                    }
                    if ($amount != $amountRequest) {
                        unset($items[$key]);
                    }
                }
            } else {
                $items = [];
            }
            if (count($items) > 0) {
                return response('Duplicated with TransactionJournal ' . $items->first()->id . '.', 403);
            } else {
                $transactionTypes = config('dearbudget.transactionTypes');
                $item = true;
                $error = '';
                $transactions = [];
                if ($item) {
                    foreach ($transactionJournal['transactions'] as $key => $value) {
                        $credit_account = Account::where('name', $value['credit_account_name'])->first();
                        $debit_account = Account::where('name', $value['debit_account_name'])->first();

                        if (array_key_exists('subcategory_name', $value))
                            $subcategory = Subcategory::where('name', $value['subcategory_name'])->first();
                        else
                            $subcategory = null;

                        if ($credit_account) {
                            if ($debit_account) {
                                //both accounts exists, but can't be both income and expense, in this case we fail:
                                if ($credit_account->role == 'incomeAccount' && $debit_account->role == 'expenseAccount') {
                                    $error .= 'Transaction ' . $key . ' can\'t have both accounts as income and expense account.';
                                } elseif ($credit_account->role == 'incomeAccount') {
                                    //this is an income transaction
                                    $transactions[$key]['credit_account_id'] = $credit_account->id;
                                    $transactions[$key]['debit_account_id'] = $debit_account->id;
                                    $transactions[$key]['amount'] = $value['amount'];
                                    $transactions[$key]['subcategory_id'] = $subcategory->id ?? null;
                                    $transactions[$key]['type'] = array_search('income', array_column($transactionTypes, 'type'));
                                } elseif ($debit_account->role == 'expenseAccount') {
                                    //this is an expense transaction
                                    $transactions[$key]['credit_account_id'] = $credit_account->id;
                                    $transactions[$key]['debit_account_id'] = $debit_account->id;
                                    $transactions[$key]['amount'] = $value['amount'];
                                    $transactions[$key]['subcategory_id'] = $subcategory->id ?? null;
                                    $transactions[$key]['type'] = array_search('expense', array_column($transactionTypes, 'type'));
                                } else {
                                    //well it only can be a transfer now
                                    if ($debit_account->id != $credit_account->id) {
                                        $transactions[$key]['credit_account_id'] = $credit_account->id;
                                        $transactions[$key]['debit_account_id'] = $debit_account->id;
                                        $transactions[$key]['amount'] = $value['amount'];
                                        $transactions[$key]['subcategory_id'] = null;
                                        $transactions[$key]['type'] = array_search('transfer', array_column($transactionTypes, 'type'));
                                    } else {
                                        $error .= 'Transaction ' . $key . ' can\'t have credit account equal to debit account.';
                                    }
                                }
                            } else {
                                if ($credit_account->role == 'incomeAccount') {
                                    $error .= 'Transaction ' . $key . ' can\'t have both accounts as income and expense account.';
                                } else {
                                    //this is an expense transaction
                                    $transactions[$key]['credit_account_id'] = $credit_account->id;
                                    $transactions[$key]['debit_account_name'] = $value['debit_account_name'];
                                    $transactions[$key]['amount'] = $value['amount'];
                                    $transactions[$key]['subcategory_id'] = $subcategory->id ?? null;
                                    $transactions[$key]['type'] = array_search('expense', array_column($transactionTypes, 'type'));
                                }
                            }
                        } else {
                            if ($debit_account->role == 'expenseAccount') {
                                $error .= 'Transaction ' . $key . ' can\'t have both accounts as income and expense account.';
                            } else {
                                //this is an income transaction
                                $transactions[$key]['credit_account_name'] = $value['credit_account_name'];
                                $transactions[$key]['debit_account_id'] = $debit_account->id;
                                $transactions[$key]['amount'] = $value['amount'];
                                $transactions[$key]['subcategory_id'] = $subcategory->id ?? null;
                                $transactions[$key]['type'] = array_search('income', array_column($transactionTypes, 'type'));
                            }
                        }
                    }
                    if (strlen($error))
                        return response($error, 400);
                    //Well, now that we validate each transaction possible, let's create missing accounts and such
                    //First, we create the Transaction Journal:
                    $transactionJournal = $this->itemClass::create($request->all());
                    foreach ($transactions as $key => $value) {
                        if (($value['credit_account_name'] ?? null) != null) {
                            $currencies = Currency::where('active', 1)->orderBy('default', 'DESC')->get();
                            $account = Account::create([
                                'name'       => $value['credit_account_name'],
                                'role'       => 'incomeAccount',
                                'curreny_id' => $currencies[0]->id,
                                'user_id'    => Auth::user()->id
                            ]);

                            //it doesn't exist yet:
                            $subTransactionJournal = TransactionsJournal::create([
                                'user_id'   => Auth::user()->id,
                                'date' => $transactionJournal->date,
                                'description' => __('Opening Balance')
                            ]);
                            Transaction::create([
                                'debit_account_id' => $account->id,
                                'type' => array_search('initialBalance', array_column($transactionTypes, 'type')),
                                'transactions_journal_id' => $subTransactionJournal->id,
                                'amount' => 0
                            ]);
                            $value['credit_account_id'] = $account->id;
                        }
                        if (($value['debit_account_name'] ?? null) != null) {
                            $currencies = Currency::where('active', 1)->orderBy('default', 'DESC')->get();
                            $account = Account::create([
                                'name'       => $value['debit_account_name'],
                                'role'       => 'expenseAccount',
                                'curreny_id' => $currencies[0]->id,
                                'user_id'    => Auth::user()->id
                            ]);

                            //it doesn't exist yet:
                            $subTransactionJournal = TransactionsJournal::create([
                                'user_id'   => Auth::user()->id,
                                'date' => $transactionJournal->date,
                                'description' => __('Opening Balance')
                            ]);
                            Transaction::create([
                                'debit_account_id' => $account->id,
                                'type' => array_search('initialBalance', array_column($transactionTypes, 'type')),
                                'transactions_journal_id' => $subTransactionJournal->id,
                                'amount' => 0
                            ]);
                            $value['debit_account_id'] = $account->id;
                        }
                        $value['transactions_journal_id'] = $transactionJournal->id;
                        Transaction::create($value);
                    }
                    return response('Item created', 201);
                } else
                    return response('Failed', 500);
            }
        }
    }

    /**
     * @OA\Get(
     *      path="/transactions/{transactionID}",
     *      tags={"Transactions"},
     *      summary="Get a specific transaction",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="transactionID",
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
        $item = $this->itemClass::findOrFail($id);

        if ($item->user_id != Auth::user()->id)
            return response('Unauthorized', 403);
        else
            return $item;
    }

    /**
     * @OA\Put(
     *      path="/transactions/{transactionID}",
     *      tags={"Transactions"},
     *      summary="Update a transaction",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="transactionID",
     *         required=true,
     *         @OA\Schema(
     *           type="integer",
     *           format="int64"
     *         )
     *     ),
     *    @OA\RequestBody(
     *       @OA\MediaType(
     *           mediaType="application/x-www-form-urlencoded",
     *           @OA\Schema(
     *              @OA\Property(property="date", type="date"),
     *              @OA\Property(property="budget_date", type="date"),
     *              @OA\Property(property="description", type="string"),
     *              @OA\Property(property="transaction_number", type="string"),
     *              @OA\Property(
     *                  property="transactions",
     *                  type="array",
     *                  @OA\Items(
     *                      type="object",
     *                      @OA\Property(
     *                          property="credit_account_name",
     *                          description="Name of an existing account or a new income account one",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="debit_account_name",
     *                          description="Name of and existing account or a new debit account one",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="amount",
     *                          description="Value of Transaction",
     *                          type="number"
     *                      ),
     *                      @OA\Property(
     *                          property="subcategory_name",
     *                          description="Name of subcategory, if it doesn't exist or if this is a transfer, it'll be created without one",
     *                          type="string"
     *                      )
     *                  )
     *              ),
     *              required={"date", "description"}
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
        $existingTransaction = $this->itemClass::findOrFail($id);
        $request['user_id'] = Auth::user()->id;
        if ($existingTransaction->user_id != Auth::user()->id) {
            return response('You cannot change another user transaction.', 403);
        }
        $transactionJournal = $request->all();
        //Quick and dirty fix for Swagger (adding items to array):
        if (substr($transactionJournal['transactions'], 0, 1) != '[') {
            $transactionJournal['transactions'] = '[' . $transactionJournal['transactions'] . ']';
        }
        $transactionJournal['transactions'] = json_decode($transactionJournal['transactions'], true);

        $validation = Validator::make($transactionJournal, [
            'date' => 'required',
            'description' => 'required',
            'transactions.0.credit_account_name' => 'required',
            'transactions.0.debit_account_name' => 'required',
            'transactions.0.amount' => 'required'
        ]);

        if ($validation->fails()) {
            return response('Missing parameters: ' . $validation->errors());
        } else {
            $transactionTypes = config('dearbudget.transactionTypes');
            $item = true;
            $error = '';
            $transactions = [];
            if ($item) {
                foreach ($transactionJournal['transactions'] as $key => $value) {
                    $credit_account = Account::where('name', $value['credit_account_name'])->first();
                    $debit_account = Account::where('name', $value['debit_account_name'])->first();
                    $subcategory = Subcategory::where('name', $value['subcategory_name'])->first();

                    if ($credit_account) {
                        if ($debit_account) {
                            //both accounts exists, but can't be both income and expense, in this case we fail:
                            if ($credit_account->role == 'incomeAccount' && $debit_account->role == 'expenseAccount') {
                                $error .= 'Transaction ' . $key . ' can\'t have both accounts as income and expense account.';
                            } elseif ($credit_account->role == 'incomeAccount') {
                                //this is an income transaction
                                $transactions[$key]['credit_account_id'] = $credit_account->id;
                                $transactions[$key]['debit_account_id'] = $debit_account->id;
                                $transactions[$key]['amount'] = $value['amount'];
                                $transactions[$key]['subcategory_id'] = $subcategory->id ?? null;
                                $transactions[$key]['type'] = array_search('income', array_column($transactionTypes, 'type'));
                            } elseif ($debit_account->role == 'expenseAccount') {
                                //this is an expense transaction
                                $transactions[$key]['credit_account_id'] = $credit_account->id;
                                $transactions[$key]['debit_account_id'] = $debit_account->id;
                                $transactions[$key]['amount'] = $value['amount'];
                                $transactions[$key]['subcategory_id'] = $subcategory->id ?? null;
                                $transactions[$key]['type'] = array_search('expense', array_column($transactionTypes, 'type'));
                            } else {
                                //well it only can be a transfer now
                                $transactions[$key]['credit_account_id'] = $credit_account->id;
                                $transactions[$key]['debit_account_id'] = $debit_account->id;
                                $transactions[$key]['amount'] = $value['amount'];
                                $transactions[$key]['subcategory_id'] = null;
                                $transactions[$key]['type'] = array_search('transfer', array_column($transactionTypes, 'type'));
                            }
                        } else {
                            if ($credit_account->role == 'incomeAccount') {
                                $error .= 'Transaction ' . $key . ' can\'t have both accounts as income and expense account.';
                            } else {
                                //this is an expense transaction
                                $transactions[$key]['credit_account_id'] = $credit_account->id;
                                $transactions[$key]['debit_account_name'] = $value['debit_account_name'];
                                $transactions[$key]['amount'] = $value['amount'];
                                $transactions[$key]['subcategory_id'] = $subcategory->id ?? null;
                                $transactions[$key]['type'] = array_search('expense', array_column($transactionTypes, 'type'));
                            }
                        }
                    } else {
                        if ($debit_account->role == 'expenseAccount') {
                            $error .= 'Transaction ' . $key . ' can\'t have both accounts as income and expense account.';
                        } else {
                            //this is an income transaction
                            $transactions[$key]['credit_account_name'] = $value['credit_account_name'];
                            $transactions[$key]['debit_account_id'] = $debit_account->id;
                            $transactions[$key]['amount'] = $value['amount'];
                            $transactions[$key]['subcategory_id'] = $subcategory->id ?? null;
                            $transactions[$key]['type'] = array_search('income', array_column($transactionTypes, 'type'));
                        }
                    }
                }
                if (strlen($error))
                    return response($error, 400);
                //now lets erase all existing transactions and we'll recreate after
                foreach ($existingTransaction->transactions as $key => $value) {
                    $value->delete();
                }
                //Well, now that we validate each transaction possible, let's create missing accounts and such
                $existingTransaction->update($request->all());
                $transactionJournal = $existingTransaction;
                foreach ($transactions as $key => $value) {
                    if (($value['credit_account_name'] ?? null) != null) {
                        $currencies = Currency::where('active', 1)->orderBy('default', 'DESC')->get();
                        $account = Account::create([
                            'name'       => $value['credit_account_name'],
                            'role'       => 'incomeAccount',
                            'curreny_id' => $currencies[0]->id,
                            'user_id'    => Auth::user()->id
                        ]);

                        //it doesn't exist yet:
                        $subTransactionJournal = TransactionsJournal::create([
                            'user_id'   => Auth::user()->id,
                            'date' => $transactionJournal->date,
                            'description' => __('Opening Balance')
                        ]);
                        Transaction::create([
                            'debit_account_id' => $account->id,
                            'type' => array_search('initialBalance', array_column($transactionTypes, 'type')),
                            'transactions_journal_id' => $subTransactionJournal->id,
                            'amount' => 0
                        ]);
                        $value['credit_account_id'] = $account->id;
                    }
                    if (($value['debit_account_name'] ?? null) != null) {
                        $currencies = Currency::where('active', 1)->orderBy('default', 'DESC')->get();
                        $account = Account::create([
                            'name'       => $value['debit_account_name'],
                            'role'       => 'expenseAccount',
                            'curreny_id' => $currencies[0]->id,
                            'user_id'    => Auth::user()->id
                        ]);

                        //it doesn't exist yet:
                        $subTransactionJournal = TransactionsJournal::create([
                            'user_id'   => Auth::user()->id,
                            'date' => $transactionJournal->date,
                            'description' => __('Opening Balance')
                        ]);
                        Transaction::create([
                            'debit_account_id' => $account->id,
                            'type' => array_search('initialBalance', array_column($transactionTypes, 'type')),
                            'transactions_journal_id' => $subTransactionJournal->id,
                            'amount' => 0
                        ]);
                        $value['debit_account_id'] = $account->id;
                    }
                    $value['transactions_journal_id'] = $transactionJournal->id;
                    Transaction::create($value);
                }
                return response('Item updated', 200);
            } else
                return response('Failed', 500);
        }
    }

    /**
     * @OA\Delete(
     *      path="/transactions/{transactionID}",
     *      tags={"Transactions"},
     *      summary="Delete a specific item",
     *      security={{"bearer_token":{}}},
     *      @OA\Parameter(
     *         in="path",
     *         name="transactionID",
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
        $item = $this->itemClass::findOrFail($id);
        if ($item->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            $item->delete();
    }
}
