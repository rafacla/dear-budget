<?php

namespace App\Http\Livewire;

use DateTime;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Budget;
use App\Helpers\Helper;
use App\Models\Transaction;
use Illuminate\Support\Facades\Route;

class Budgets extends Component
{
    public $modelClass = Budget::class;
    public $itemClassName = 'Budget';
    public $transactionTypes;
    public $currentDate;
    public $items;
    public $toBudget = 0;
    public $incomeCumulative = 0;
    public $incomeMonth = 0;
    public $overspentCumulative = 0;
    public $overspentLMonth = 0;
    public $budgetedCumulative = 0;
    public $budgetedMonth = 0;
    public $budgets = [];
    public $transactions = [];
    public $databaseBudgets = [];
    public $databaseTransactions = [];
    public $available = [];
    public $itemID;
    public $form = array(
        'budget_value'          => '',
        'subcategory_id'        => '',
    );
    public $isOpen = 0;

    public function getTransactions() {
        $this->transactionTypes = config('dearbudget.transactionTypes');
        $this->databaseTransactions = Transaction::whereHas('transactionsJournal', function($q) {
            $q->where('budget_date', '<=',$this->currentDate)
              ->where('budget_date','null')
                ->orWhere('date','<=',$this->currentDate);
        })->with('transactionsJournal')->get()->toArray();
        $this->incomeCumulative = 0;

        foreach ($this->databaseTransactions as $value) {
            if ($value['type'] == array_search('expense',array_column($this->transactionTypes,'type'))) {
                if (
                    $value['transactions_journal']['budget_date'] != null && (new DateTime($value['transactions_journal']['budget_date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1')
                        || ($value['transactions_journal']['budget_date'] == null
                            && (new DateTime($value['transactions_journal']['date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1'))
                    ) {
                        if ($value['subcategory_id'] != null) {
                            $this->transactions[$value['subcategory_id']] = ($this->transactions[$value['subcategory_id']] ?? 0) + $value['amount'];
                        }
                }
            } elseif (
                $value['type'] == array_search('income',array_column($this->transactionTypes,'type'))
                || ($value['type'] == array_search('initialBalance',array_column($this->transactionTypes,'type'))
                    && $value['amount'] > 0
                    )
                ) {
                $this->incomeCumulative += $value['amount'];
                if (
                    $value['transactions_journal']['budget_date'] != null && (new DateTime($value['transactions_journal']['budget_date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1')
                        || ($value['transactions_journal']['budget_date'] == null
                            && (new DateTime($value['transactions_journal']['date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1'))
                    ) {
                    $this->incomeMonth += $value['amount'];
                }
            }
        }
    }

    public function getBudgets() {
        //First we get all transactions up to today
        $this->getTransactions();
        //Then we get all the budgets on db
        $cumulativeBudgets = Auth::user()->budgets($this->currentDate, true);
        //Finally we discover when user first started budgeting
        //TODO: If user has been budgeting for 30 years, 300 transactions/30 budgets a month would it laggy this - should we check?
        //Meaning: 360 months and 10,800 transactions and 1,080 budgets - sounds fair?
        $dates = array_column(array_column($this->databaseTransactions,'transactions_journal'),'budget_date');
        $dates = array_merge($dates, array_column(array_column($this->databaseTransactions,'transactions_journal'),'date'));
        $dates = array_merge($dates, array_column($cumulativeBudgets->toArray(),'date'));
        if (count($dates) > 0)
            $startDate = (new DateTime(min(array_filter($dates))));
        else
            $startDate = (new DateTime('now'));

        //Ok! We found our starting point, now the magic begins:

    }

    public function mount($year = null,$month = null) {
        $this->transactionTypes = config('dearbudget.transactionTypes');
        if ($year != null)
            $this->currentDate  = (new DateTime($year.'-'.$month.'-01'))->modify('last day of this month');
        else
            $this->currentDate = new DateTime('last day of this month');

        $this->getBudgets();

        $this->items = Auth::user()->categories->where('expense',true);

        foreach ($this->items as $item) {
            foreach ($item->subcategories as $subcategory) {
                $budgetValue = $subcategory->budgets($this->currentDate->format('Y-m-1'))->first()->budget_value ?? 0;
                $this->budgets[$subcategory->id] = number_format($budgetValue,2,'.','');
                $this->available[$subcategory->id] = number_format($budgetValue,2,'.','');
            }
        }
        $this->toBudget = $this->incomeCumulative - $this->budgetedCumulative - $this->overspentCumulative;
    }

    public function render() {
        return view('livewire.budgets.list');
    }

    public function updatedBudgets($value, $name)
    {
        $this->modelClass::updateOrCreate(['date' => date('Y-m-1',$this->currentDate), 'subcategory_id' => $name, 'user_id' => Auth::user()->id],
         ['budget_value' => $value]);
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
}
