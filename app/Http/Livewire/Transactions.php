<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\TransactionsJournal;
use App\Models\Account;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Route;

class Transactions extends Component
{
    protected $listeners = [
        'selectedAccount' => 'updateField',

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
    public $isOpen = 0;

    //Function to receive from AutoComplete component selected Value and update $form Object
    public function updateField($field) {
        $path = &$this->form;
        $nestedPath = explode("-",$field['wiredTo']);

        foreach ($nestedPath as $value) {
            $path = &$path[$value];
        }
        $path = $field['selectedAccount'] != null ? Account::find($field['selectedAccount']['id']) : null;
        //Wonderful: as we received user choice, now we've got to validate it, meaning:
        //1) Credit Account can't be equal to Debit Account
        //2) TODO: If Credit Account = Income Acconunt, Debit Account can't be Expense Account or Vice-Versa (At least I guess)
        if ($nestedPath[2] == 'credit_account') {
            if ($this->form['transactions'][$nestedPath[1]]['debit_account']['id'] == $path->id) {
                $this->form['transactions'][$nestedPath[1]]['debit_account'] = null;
            }
        } else {
            if ($this->form['transactions'][$nestedPath[1]]['credit_account']['id'] == $path->id) {
                $this->form['transactions'][$nestedPath[1]]['credit_account'] = null;
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
            'form.date'                 => 'required',
            'form.subcategory_id'       => 'required',
            'form.budget'                 => 'required',
            'form.statementDueDay'      => 'exclude_unless:form.role,creditCard|required',
            'form.statementClosingDay'  => 'exclude_unless:form.role,creditCard|required'
        ]);

        $this->form['user_id'] = Auth::user()->id;
        if (!is_int($this->form['statementClosingDay']))
            $this->form['statementClosingDay'] = null;
        if (!is_int($this->form['statementDueDay']))
            $this->form['statementDueDay'] = null;
        if (!is_int($this->form['bank_id']))
            $this->form['bank_id'] = null;
        $this->modelClass::updateOrCreate(['id' => $this->itemID], $this->form);

        session()->flash('message',
            $this->itemID ? __($this->itemClassName.' updated successfully.') : __($this->itemClassName.' created Successfully.'));

        $this->closeModal();
        $this->resetInputFields();
    }

    public function new() {
        $this->resetInputFields();
        $this->isOpen = true;
        $this->emit('editTransaction', $transactionJournalId = null);
    }

    private function resetInputFields() {
        foreach ($this->form as $key => $value) {
            if (is_array($this->form[$key])) {
                if ($key == 'transactions') {
                    $this->form[$key] = [[
                        'credit_account'         => null,
                        'debit_account'          => null,
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
        $item = $this->modelClass::findOrFail($id);
        $this->itemID = $id;
        foreach ($this->form as $key => $value) {
            if ($key == 'transactions') {
                $transactions = [];
                foreach ($item[$key] as $transaction) {
                    array_push($transactions, [
                        'id'    => $transaction->id,
                        'credit_account' => $transaction->creditAccount,
                        'debit_account' => $transaction->debitAccount,
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

    public function delete($id)
    {
        $this->modelClass::find($id)->delete();
        session()->flash('message', $this->itemClassName.' deleted successfully.');
    }

}
