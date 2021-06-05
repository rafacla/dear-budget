<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\TransactionsJournal;
use App\Models\Account;
use App\Helpers\Helper;
use App\Models\Subcategory;
use App\Models\Transaction;
use App\Models\Currency;
use Illuminate\Support\Facades\Route;

class Transactions extends Component
{
    protected $listeners = [
        'selectedAccount' => 'updateAccount',
        'selectedSubcategory' => 'updateSubcategory'
      ];
    public $modelClass = TransactionsJournal::class;
    public $itemClassName = 'Transactions';
    public $assetAccounts;
    public $expenseAccounts;
    public $incomeAccounts;
    public $accountRoles;
    public $transactionTypes;
    public $selected = [];
    public $selectedAll;
    public $currentDate;
    public $items;
    public $itemID;
    public $form = array(
        'date'                   => '',
        'user_id'                => '',
        'budget_date'            => '',
        'description'            => '',
        'transaction_number'     => '',
        'transactions'           => []
    );
    public $transactionsValidation;
    public $isOpen = 0;

    //Function to receive from AutoComplete component selected Value and update $form Object
    public function updateSubcategory($field) {
        $path = &$this->form;
        $nestedPath = explode("-",$field['wiredTo']);

        foreach ($nestedPath as $value) {
            $path = &$path[$value];
        }
        $path = $field['selectedSubcategory'] != null ? Subcategory::find($field['selectedSubcategory']['id']) : null;
    }
    //Function to receive from AutoComplete component selected Value and update $form Object
    public function updateAccount($field) {
        $nestedPath = explode("-",$field['wiredTo']);
        $this->form[$nestedPath[0]][$nestedPath[1]][$nestedPath[2]] = $field['selectedAccount'] != null ? Account::find($field['selectedAccount']['id']) : null;
        $this->form[$nestedPath[0]][$nestedPath[1]][$nestedPath[2] . "_name"] = $field['query'];

        $path = $this->form[$nestedPath[0]][$nestedPath[1]][$nestedPath[2]];
        $pathName = $this->form[$nestedPath[0]][$nestedPath[1]][$nestedPath[2] . "_name"];
        //Wonderful: as we received user choice, now we've got to validate it, meaning:
        //1) Credit Account can't be equal to Debit Account
        //2) TODO: If Credit Account = Income Acconunt, Debit Account can't be Expense Account or Vice-Versa (At least I guess)
        if ($nestedPath[2] == 'credit_account') {
            if ($this->form['transactions'][$nestedPath[1]]['debit_account'] != null && $path != null &&
                    $this->form['transactions'][$nestedPath[1]]['debit_account']['id'] == $path->id) {
                $this->form['transactions'][$nestedPath[1]]['debit_account'] = null;
                $this->form['transactions'][$nestedPath[1]]['debit_account_name'] = '';
                $this->emit('updatedSelectedAccount', ['nestedPath' => $nestedPath[0] . '-' . $nestedPath[1] . '-debit_account' , 'value' => null]);
            }
        } else {
            if ($this->form['transactions'][$nestedPath[1]]['credit_account'] != null && $path != null &&
                    $this->form['transactions'][$nestedPath[1]]['credit_account']['id'] == $path->id) {
                $this->form['transactions'][$nestedPath[1]]['credit_account'] = null;
                $this->form['transactions'][$nestedPath[1]]['credit_account_name'] = '';
                $this->emit('updatedSelectedAccount', ['nestedPath' => $nestedPath[0] . '-' . $nestedPath[1] . '-credit_account' , 'value' => null]);
            }
        }
    }

    //function to update the checkbox, not really reliable, TODO: study a better way to control this objects using Alpine.JS
    public function updatedSelectedAll($selectedAllValue) {
        foreach ($this->items as $item) {
            $this->selected[$item->id] = $selectedAllValue;
        }
    }
    public function updatedSelected($value, $key) {
        $allSelected = true;
        foreach ($this->selected as $key => $value) {
            if (!$value)
                $allSelected = false;
        }
        $this->selectedAll = $allSelected;
    }

    public function mount($year = null,$month = null) {
        if ($year != null)
            $this->currentDate  = mktime(0,0,0,$month,1,$year);
        else
            $this->currentDate = time();
        $this->transactionsValidation = '';
        $this->accountRoles = config('dearbudget.accountRoles');
        $this->transactionTypes = config('dearbudget.transactionTypes');
        $this->assetAccounts = Account::where('role','checkingAccount')
        ->orWhere('role','walletCash')
        ->orWhere('role','investmentAccount')
        ->orWhere('role','creditCard')
        ->get()->toJSON();
        $this->expenseAccounts = Account::where('role','expenseAccount')->get()->toJSON();
        $this->incomeAccounts = Account::where('role','incomeAccount')->get()->toJSON();

        $filter = [
            [
                'filterField'   => 'deleted_at',
                'filterAs'      => '=',
                'filterTo'      => null
            ],
            [
                'filterField'   => 'date',
                'filterAs'      => '>=',
                'filterTo'      => date('Y-m-1',$this->currentDate)
            ],
            [
                'filterField'   => 'date',
                'filterAs'      => '<=',
                'filterTo'      => date('Y-m-t',$this->currentDate)
            ],
            ];
        $this->items = Auth::user()->transactionsJournals($filter);
        foreach ($this->items as $item) {
            $this->selected[$item->id] = false;
        }
        $this->selectedAll = false;
    }

    public function render()
    {
        return view('livewire.transactions.list');
    }

    public function store()
    {
        $this->validate([
            'form.date'                             => 'required',
            'form.description'                      => 'required',
            'form.transactions'                     => 'required|array|min:1'
        ]);

        $validated = true;
        //ok we validated transaction journal, let's validate each transaction now:
        foreach ($this->form['transactions'] as $key => $value) {
            if (($value['credit_account'] == null && $value['credit_account_name'] != null)
                || ($value['credit_account'] != null && $value['credit_account']['role'] == 'incomeAccount')
            ) {
                if (($value['debit_account'] == null && $value['debit_account_name'] != null)
                    || ($value['debit_account'] != null && $value['debit_account']['role'] == 'expenseAccount')) {
                        $validated = false;
                        $this->transactionsValidation = __('You can\'t have a transaction that transfers money from an income account to an expense account');
                }

            }
            if ($value['credit_account'] != null && $value['debit_account'] != null && $value['debit_account'] == $value['credit_account']) {
                $validated = false;
                $this->transactionsValidation = __('You can\'t have a transaction that transfers money from and to the same account');
            }
            if ($value['credit_account'] == null && $value['credit_account_name'] == null) {
                $validated = false;
                $this->transactionsValidation = __('You have to define a credit account for all transactions');
            }
            if ($value['debit_account'] == null && $value['debit_account_name'] == null) {
                $validated = false;
                $this->transactionsValidation = __('You have to define a debit account for all transactions');
            }
        }

        if ($validated) {
            $this->form['user_id'] = Auth::user()->id;
            if ($this->form['budget_date'] == '')
                $this->form['budget_date'] = null;
            $transactionJournal = $this->modelClass::updateOrCreate(['id' => $this->itemID], $this->form);

            //Ok, we created or updated the Transaction Journal... now we have to create the transactions:
            foreach ($transactionJournal->transactions as $transaction) {
                //first we gonna find each transactions that have been deleted or updated:
                $delete = true;
                foreach ($this->form['transactions'] as $updatedTransaction) {
                    if ($updatedTransaction['id'] == $transaction->id) {
                        $delete = false;
                    }
                }
                if ($delete) {
                    Transaction::find($transaction->id)->delete();
                }
            }
            //Now we have to create the new transactions:
            foreach ($this->form['transactions'] as $updatedTransaction) {
                if ($updatedTransaction['credit_account'] == null && $updatedTransaction['credit_account_name'] != null) {
                    $currencies = Currency::where('active', 1)->orderBy('default', 'DESC')->get();
                    $account = Account::create([
                        'name'       => $updatedTransaction['credit_account_name'],
                        'role'       => 'incomeAccount',
                        'curreny_id' => $currencies[0]->id,
                        'user_id'    => Auth::user()->id
                    ]);

                    //it doesn't exist yet:
                    $transactionJournal = TransactionsJournal::create([
                        'user_id'   => Auth::user()->id,
                        'date' => $this->form['date'],
                        'description' => __('Opening Balance')
                    ]);
                    $updatedTransaction['credit_account'] = Transaction::create([
                        'debit_account_id' => $account->id,
                        'type' => array_search('initialBalance',array_column($this->transactionTypes,'type')),
                        'transactions_journal_id' => $transactionJournal->id,
                        'amount' => 0
                    ]);
                }
                Transaction::updateOrCreate(['id' => $updatedTransaction['id']], $updatedTransaction);
            }

            session()->flash('message',
                $this->itemID ? __($this->itemClassName.' updated successfully.') : __($this->itemClassName.' created successfully.'));
            $this->mount();
            $this->closeModal();
            $this->resetInputFields();
        }
    }

    public function closeModal() {
        $this->isOpen = false;
    }
    public function new() {
        $this->resetInputFields();
        $this->isOpen = true;
    }

    private function resetInputFields() {
        foreach ($this->form as $key => $value) {
            if (is_array($this->form[$key])) {
                if ($key == 'transactions') {
                    $value = [];
                    $this->form[$key] = [[
                        'credit_account'         => null,
                        'debit_account'          => null,
                        'credit_account_name'    => null,
                        'debit_account_name'    => null,
                        'transactions_journal_id'   => '',
                        'amount'                    => 0,
                        'subcategory'            => ''
                    ]];
                } else {
                    $this->form[$key] = [];
                }
            } else {
                $this->form[$key] = '';
            }
        }

    }

    public function edit($id)
    {
        $this->transactionsValidation = '';
        $item = $this->modelClass::findOrFail($id);
        $this->itemID = $id;
        foreach ($this->form as $key => $value) {
            if ($key == 'transactions') {
                $transactions = [];
                foreach ($item[$key] as $transaction) {
                    array_push($transactions, [
                        'id'    => $transaction->id,
                        'credit_account' => $transaction->creditAccount,
                        'credit_account_name' => $transaction->creditAccount->name,
                        'debit_account' => $transaction->debitAccount,
                        'debit_account_name' => $transaction->debitAccount->name,
                        'amount' => $transaction->amount,
                        'subcategory' => $transaction->subcategory,
                    ]);
                }
                $this->form[$key] = $transactions;
            } else {
                $this->form[$key] = $item[$key];
            }
        }
        $this->isOpen = true;
        $this->emit('editTransaction', $transactionJournalId = $id);
    }

    public function addTransaction() {
        array_push($this->form['transactions'],
            [
                'credit_account'         => null,
                'debit_account'          => null,
                'transactions_journal_id'   => '',
                'amount'                    => 0,
                'subcategory'            => ''
            ]
        );
    }

    public function deleteTransaction($id) {
        unset($this->form['transactions'][$id]);
    }

    public function delete($id)
    {
        $this->modelClass::find($id)->delete();
        session()->flash('message', $this->itemClassName.' deleted successfully.');
    }

}
