<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Account;

class AccountAutoComplete extends Component
{
    public $initialQuery;
    public $query;
    public $accounts;
    public $highlightIndex;
    public $openSuggestions;
    public $wiredTo;
    public $showExpenseAccounts = true;
    public $showIncomeAccounts = true;
    public $hasIncomeAccounts;
    public $hasExpenseAccounts;
    public $hasAssetAndLiabiliyAccounts;
    public function resetTo() {
        $this->query = $this->initialQuery;
        $this->accounts = [];
        $this->highlightIndex = 0;
        $this->openSuggestions = false;
        $this->hasAssetAndLiabiliyAccounts = false;
        $this->hasExpenseAccounts = false;
        $this->hasIncomeAccounts = false;
    }

    public function incrementHighlight() {
        $this->highlightIndex++;
        if ($this->highlightIndex > (count($this->accounts) - 1)) {
            $this->highlightIndex = 0;
        }
    }

    public function decrementHighlight() {
        $this->highlightIndex--;
        if ($this->highlightIndex < 0) {
            $this->highlightIndex = (count($this->accounts) - 1);
        }
    }

    public function selectItem($selectedItem = null) {
        if ($selectedItem == null) {
            $selectedItem = $this->highlightIndex;
        }
        $account = $this->accounts[$selectedItem] ?? null;
        if ($account != null)
            $this->query = $account['name'];
        $this->openSuggestions = false;
        $this->emit('selectedAccount', ['wiredTo' => $this->wiredTo, 'selectedAccount' => $account]);
    }

    public function mount() {
        $this->resetTo();
    }

    public function updatedQuery() {
        $this->hasAssetAndLiabiliyAccounts = false;
        $this->hasIncomeAccounts = false;
        $this->hasExpenseAccounts = false;
        $this->accounts = Account::where('name', 'like', '%' . $this->query . '%');
        if (!$this->showExpenseAccounts)
            $this->accounts->where('role','!=','expenseAccount');
        if (!$this->showIncomeAccounts)
            $this->accounts->where('role','!=','incomeAccount');
        $this->accounts = $this->accounts
            ->orderBy('name')
            ->get()
            ->toArray();
        $this->openSuggestions = true;
        usort($this->accounts, function ($a, $b) {
            if ($a['role'] == 'expenseAccount' || $a['role'] == 'incomeAccount') {
                if ($a['role'] == 'incomeAccount' || $b['role'] == 'incomeAccount')
                    $this->hasIncomeAccounts = true;
                if ($a['role'] == 'expenseAccount' || $b['role'] == 'expenseAccount')
                    $this->hasExpenseAccounts = true;
                if ($b['role'] == 'expenseAccount' || $b['role'] == 'incomeAccount') {
                    return 0;
                } else {
                    return -1;
                }
            }
            $this->hasAssetAndLiabiliyAccounts = true;
            return 1;
        });
    }

    public function render()
    {
        return view('livewire.account-auto-complete');
    }
}
