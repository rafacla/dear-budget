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

    public function resetTo() {
        $this->query = $this->initialQuery;
        $this->accounts = [];
        $this->highlightIndex = 0;
        $this->openSuggestions = false;
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
        $this->accounts = Account::where('name', 'like', '%' . $this->query . '%')
            ->get()
            ->toArray();
        $this->openSuggestions = true;
    }

    public function render()
    {
        return view('livewire.account-auto-complete');
    }
}
