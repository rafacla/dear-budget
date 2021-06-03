<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Bank;
use App\Models\Currency;
use App\Models\Account;
use App\Models\TransactionsJournal;
use App\Models\Transaction;

class Accounts extends Component
{
    public $modelClass = Account::class;
    public $itemClassName = 'Account';
    public $accountFilter = 'assetLiabilityAccount';
    public $transactionTypes;
    public $accountRoles;
    public $banks;
    public $currencies;
    public $items;
    public $itemID;
    public $openingBalanceTransactionId;
    public $form = array(
        'name'                  => '',
        'description'           => '',
        'currency_id'           => '',
        'number'                => '',
        'role'                  => '',
        'bank_id'               => '',
        'user_id'               => '',
        'statementClosingDay'   => null,
        'statementDueDay'       => null,
        'openingbalance'        => 0,
        'openingbalancedate'    => ''
    );
    public $isOpen = 0;

    public function render()
    {
        $this->currencies = Currency::where('active', 1)->orderBy('default', 'DESC')->get();
        $this->accountRoles = config('dearbudget.accountRoles');
        $this->banks = Bank::all();
        $this->transactionTypes = config('dearbudget.transactionTypes');

        if ($this->accountFilter == 'assetLiabilityAccount')
            {
                $this->items = Auth::user()->accounts
                    ->whereIn('role',['checkingAccount','creditCard','walletCash','investmentAccount']);
            }
        elseif ($this->accountFilter == 'assetAccount')
            {
                $this->items = Auth::user()->accounts->whereIn('role',['checkingAccount','walletCash','investmentAccount']);
            }
        elseif ($this->accountFilter == 'liabilityAccount')
            {
                $this->items = Auth::user()->accounts->where('role','=','creditCard');
            }
        elseif ($this->accountFilter == 'expenseAccount')
            {
                $this->items = Auth::user()->accounts->where('role','=','expenseAccount');
            }
        else
            {
                $this->items = Auth::user()->accounts->where('role','=','incomeAccount');
            }
        return view('livewire.accounts.list');
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
        $this->itemID = '';
        $this->openingBalanceTransactionId = '';
        $this->form['openingbalance'] = number_format(0,2,".","");
        $this->form['openingbalancedate'] = Date('Y-m-d');
        $this->form['currency_id'] = $this->currencies[0]->id;
        $this->form['role'] = array_keys($this->accountRoles)[0];
    }

    public function openModal()
    {
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
    }

    private function resetInputFields(){
        foreach ($this->form as $key => $value) {
            $this->form[$key] = '';
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'form.statementDueDay.required' => 'Due Day required for Credit Card',
            'form.statementClosingDay.required' => 'Closing Day required for Credit Card',
            'form.name.required' => 'Name required',
            'form.number.required' => 'Account Number required',
            'form.currency_id.required' => 'Currency required',
            'form.role.required' => 'Account role required',
            'form.openingbalance.required' => 'Opening Balance required',
            'form.openingbalancedate.required' => 'Opening Balance Date required',
        ];
    }



    public function store()
    {
        $this->validate([
            'form.name'                 => 'required',
            'form.currency_id'          => 'required',
            'form.role'                 => 'required',
            'form.statementDueDay'      => 'exclude_unless:form.role,creditCard|required',
            'form.statementClosingDay'  => 'exclude_unless:form.role,creditCard|required',
            'form.openingbalance'       =>  'required',
            'form.openingbalancedate'   =>  'required|date'
        ]);

        $this->form['user_id'] = Auth::user()->id;

        if (!is_numeric($this->form['statementClosingDay']))
            $this->form['statementClosingDay'] = null;
        if (!is_numeric($this->form['statementDueDay']))
            $this->form['statementDueDay'] = null;
        if (!is_numeric($this->form['bank_id']))
            $this->form['bank_id'] = null;
        $account = $this->modelClass::updateOrCreate(['id' => $this->itemID], $this->form);
        if ($this->openingBalanceTransactionId == '') {
            //it doesn't exist yet:
            $transactionJournal = TransactionsJournal::create([
                'user_id'   => Auth::user()->id,
                'date' => $this->form['openingbalancedate'],
                'description' => __('Opening Balance')
            ]);
            Transaction::create([
                'debit_account_id' => $account->id,
                'type' => $initialBalanceTypeId = array_search('initialBalance',array_column($this->transactionTypes,'type')),
                'transactions_journal_id' => $transactionJournal->id,
                'amount' => $this->form['openingbalance']
            ]);
        } else {
            $transaction = Transaction::find($this->openingBalanceTransactionId);
            TransactionsJournal::updateOrCreate(['id' => $transaction->transactionsJournal->id],['date' => $this->form['openingbalancedate']]);
            Transaction::updateOrCreate(['id' => $transaction->id],['amount' => $this->form['openingbalance']]);
        }
        session()->flash('message',
            $this->itemID ? __($this->itemClassName.' updated successfully.') : __($this->itemClassName.' created Successfully.'));

        $this->closeModal();
        $this->resetInputFields();
    }

    public function edit($id)
    {
        $item = $this->modelClass::findOrFail($id);
        $initialBalanceTypeId = array_search('initialBalance',array_column($this->transactionTypes,'type'));
        $firstTransaction = Transaction::where('credit_account_id',null)
            ->where('debit_account_id',$id)
            ->where('type',$initialBalanceTypeId)
            ->first();
        $this->itemID = $id;
        foreach ($this->form as $key => $value) {
            $this->form[$key] = $item[$key];
        }
        if ($firstTransaction != null) {
            $this->openingBalanceTransactionId = $firstTransaction->id;
            $this->form['openingbalance'] = $firstTransaction->amount;
            $this->form['openingbalancedate'] = $firstTransaction->transactionsJournal->date;
        } else {
            $this->openingBalanceTransactionId = '';
            $this->form['openingbalance'] = 0;
            $this->form['openingbalancedate'] = date('Y-m-d');
        }

        $this->openModal();
    }

    public function delete($id)
    {
        $this->modelClass::find($id)->delete();
        session()->flash('message', $this->itemClassName.' deleted successfully.');
    }
}
