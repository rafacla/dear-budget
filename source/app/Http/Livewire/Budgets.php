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
    public $accountRoles;
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
    public $investmentCumulative = 0;
    public $investmentMonth = 0;
    public $transactions = [];
    public $databaseBudgets = [];
    public $databaseTransactions = [];
    public $available = [];
    public $itemID;
    public $transferredFromToInvestmentAccount = 0;
    public $form = array(
        'budget_value'          => '',
        'subcategory_id'        => '',
    );
    public $isOpen = 0;

    public function getTransactions() {
        $this->transactionTypes = config('dearbudget.transactionTypes');
        $this->accountRoles = config('dearbudget.accountRoles');
        $this->databaseTransactions = 
        Transaction::whereHas('transactionsJournal', function($q) {
            $q->where('budget_date', '<=',$this->currentDate)
                ->orWhere(function($query2) {
                    $query2->where('budget_date','=',null)
                    ->where('date', '<=',$this->currentDate);
                });
            })
            ->with('transactionsJournal')->with('subcategory')->with('creditAccount')->with('debitAccount')
            ->get()->toArray();
        $this->incomeCumulative = 0;
        foreach ($this->databaseTransactions as $value) {
            if ($value['type'] == array_search('expense',array_column($this->transactionTypes,'type'))) {
                if (
                    $value['transactions_journal']['budget_date'] != null && (new DateTime($value['transactions_journal']['budget_date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1')
                        || ($value['transactions_journal']['budget_date'] == null
                            && (new DateTime($value['transactions_journal']['date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1'))
                    ) {
                        if ($value['subcategory_id'] != null) {
                            if ($this->accountRoles[$value['credit_account']['role']]['budget']  == 'on') {
                                $this->transactions[$value['subcategory_id']] = ($this->transactions[$value['subcategory_id']] ?? 0) + ($value['amount'] ?? 0);
                            }
                        }
                }
            } elseif (
                $value['type'] == array_search('income',array_column($this->transactionTypes,'type'))
                || ($value['type'] == array_search('initialBalance',array_column($this->transactionTypes,'type'))
                    )
                ) {
                if ($this->accountRoles[$value['debit_account']['role']]['budget'] == 'on') {
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
            //Here we calculate the automatically investment budget and fixes deposits and withdrawals from the budget:
            if ($value['credit_account'] != null && $this->accountRoles[$value['credit_account']['role']]['budget']  == 'investment') {
                //money is moving out of investment budget
                //before we calculate the investment budget, we have to check if this is going for a on-budget account:
                if ($value['debit_account'] != null && $this->accountRoles[$value['debit_account']['role']]['budget']  == 'on') {
                    $this->incomeCumulative += $value['amount'];
                    if (
                        $value['transactions_journal']['budget_date'] != null && (new DateTime($value['transactions_journal']['budget_date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1')
                            || ($value['transactions_journal']['budget_date'] == null
                                && (new DateTime($value['transactions_journal']['date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1'))
                        ) {
                            $this->transferredFromToInvestmentAccount += $value['amount'];
                        }
                }
                //right, now we calculate the budget
                $this->investmentCumulative -= $value['amount'];
                if (
                    $value['transactions_journal']['budget_date'] != null && (new DateTime($value['transactions_journal']['budget_date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1')
                        || ($value['transactions_journal']['budget_date'] == null
                            && (new DateTime($value['transactions_journal']['date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1'))
                    ) {
                        $this->investmentMonth -= $value['amount'];
                    }
            } elseif ($value['debit_account'] != null && $this->accountRoles[$value['debit_account']['role']]['budget']  == 'investment') {
                //money is moving in investment budget
                //before we calculate the investment budget, we have to check if this is coming from on-budget account:
                if ($value['credit_account'] != null && $this->accountRoles[$value['credit_account']['role']]['budget']  == 'on') {
                    $this->incomeCumulative -= $value['amount'];
                    if (
                        $value['transactions_journal']['budget_date'] != null && (new DateTime($value['transactions_journal']['budget_date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1')
                            || ($value['transactions_journal']['budget_date'] == null
                                && (new DateTime($value['transactions_journal']['date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1'))
                        ) {
                            $this->transferredFromToInvestmentAccount -= $value['amount'];
                        }
                }
                //right, now we calculate the budget
                $this->investmentCumulative += $value['amount'];
                if (
                    $value['transactions_journal']['budget_date'] != null && (new DateTime($value['transactions_journal']['budget_date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1')
                        || ($value['transactions_journal']['budget_date'] == null
                            && (new DateTime($value['transactions_journal']['date']))->format('Y-m-1') == $this->currentDate->format('Y-m-1'))
                    ) {
                        $this->investmentMonth += $value['amount'];
                    }
            }
        }
    }

    public function getBudgets() {
        //First we get all transactions up to today
        $this->getTransactions();
        //Then we get all the budgets on db
        $this->databaseBudgets = Auth::user()->budgets($this->currentDate, true);
        //Finally we've to discover when user first started budgeting
        //TODO: If user has been budgeting for 30 years, 300 transactions/30 budgets a month would it laggy this - should we check?
        //Meaning: 360 months and 10,800 transactions and 1,080 budgets - sounds fair?
        $dates = array_column(array_column($this->databaseTransactions,'transactions_journal'),'budget_date');
        $dates = array_merge($dates, array_column(array_column($this->databaseTransactions,'transactions_journal'),'date'));
        $dates = array_merge($dates, array_column($this->databaseBudgets->toArray(),'date'));
        if (count($dates) > 0)
            $startDate = (new DateTime(min(array_filter($dates))));
        else
            $startDate = (new DateTime('now')); //looks like there's no budget or transaction what so ever, there's nothing to calculate

        //Ok! We found our starting point, now the magic begins:
        $loopDate = $startDate->modify('last day of last month');
        while ($loopDate <= $this->currentDate) {
            foreach ($this->databaseBudgets as $value) {
                if ((new DateTime($value['date']))->format('Y-m-1') == $loopDate->format('Y-m-1')) {
                    $this->available[$value['subcategory_id']]
                        = ($this->available[$value['subcategory_id']] ?? 0) + ($value['budget_value'] ?? 0);
                    $this->budgetedCumulative
                        = ($this->budgetedCumulative ?? 0) + ($value['budget_value'] ?? 0);
                    if ($loopDate == $this->currentDate) {
                        $this->budgets[$value['subcategory_id']] = $value['budget_value'] ?? 0;
                        $this->budgetedMonth = ($this->budgetedMonth ?? 0) + ($value['budget_value'] ?? 0);
                    }
                }
            }
            foreach ($this->databaseTransactions as $value) {
                $transactionDate = $value['transactions_journal']['budget_date'] ?? $value['transactions_journal']['date'];
                if ((new DateTime($transactionDate))->format('Y-m-1') == $loopDate->format('Y-m-1')) {
                    if ($value['type'] == array_search('expense',array_column($this->transactionTypes,'type'))) {
                        if ($this->accountRoles[$value['credit_account']['role']]['budget']  == 'on') {
                            $this->available[$value['subcategory_id']]
                                = ($this->available[$value['subcategory_id']] ?? 0) - ($value['amount'] ?? 0);
                        }
                    }
                }
            }
            //After looping all transactions on current month, let's check if there's no overspent category
            //If there's any overspent and this is not the current month, we reset it and sum as overspent
            //thus reducing amount available to budget:
            foreach ($this->available as $key => $value) {
                if ($value < 0) {
                    if ($loopDate < $this->currentDate) {
                        $this->overspentCumulative = ($this->overspentCumulative ?? 0) - $value;
                        if ((new DateTime($loopDate->format('Y-m-d')))->modify('last day of next month')->format('Y-m-1') == $this->currentDate->format('Y-m-1')) {
                            $this->overspentLMonth = ($this->overspentLMonth ?? 0) - $value;
                        }
                        $this->available[$key] = 0;
                    }
                }
            }
            $loopDate->modify('last day of next month');
        }
    }

    public function mount($year = null,$month = null) {
        $this->transactionTypes = config('dearbudget.transactionTypes');
        $this->budgets = [];
        $this->transactions = [];
        $this->databaseBudgets = [];
        $this->databaseTransactions = [];
        $this->available = [];
        $this->toBudget = 0;
        $this->transferredFromToInvestmentAccount = 0;
        $this->incomeCumulative = 0;
        $this->incomeMonth = 0;
        $this->overspentCumulative = 0;
        $this->overspentLMonth = 0;
        $this->budgetedCumulative = 0;
        $this->investmentMonth = 0;
        $this->investmentCumulative = 0;
        $this->budgetedMonth = 0;
        if ($year == null)
            $year = (new DateTime('last day of this month'))->format('Y');
        if ($month == null)
            $month = (new DateTime('last day of this month'))->format('m');
        $this->currentDate  = (new DateTime($year.'-'.$month.'-01'))->modify('last day of this month');

        $this->items = Auth::user()->categories->where('expense',true);

        $this->getBudgets();


        $this->toBudget = $this->incomeCumulative - $this->budgetedCumulative - $this->overspentCumulative;
    }

    public function render() {
        return view('livewire.budgets.list');
    }

    public function updatedBudgets($value, $name)
    {
        $this->modelClass::updateOrCreate(['date' => $this->currentDate->format('Y-m-1'), 'subcategory_id' => $name, 'user_id' => Auth::user()->id],
         ['budget_value' => floatval($value)]);
        $this->mount($this->currentDate->format('Y'), $this->currentDate->format('m'));
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
