<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Subcategory;

class CategoryAutoComplete extends Component
{
    public $initialQuery;
    public $query;
    public $subcategories;
    public $expenseCategories;
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
        $this->subcategories = [];
        $this->highlightIndex = 0;
        $this->openSuggestions = false;
    }

    public function incrementHighlight() {
        $this->highlightIndex++;
        if ($this->highlightIndex > (count($this->subcategories) - 1)) {
            $this->highlightIndex = 0;
        }
    }

    public function decrementHighlight() {
        $this->highlightIndex--;
        if ($this->highlightIndex < 0) {
            $this->highlightIndex = (count($this->subcategories) - 1);
        }
    }

    public function selectItem($selectedItem = null) {
        if ($selectedItem == null) {
            $selectedItem = $this->highlightIndex;
        }
        $subcategory = $this->subcategories[$selectedItem] ?? null;
        if ($this->openSuggestions == false || empty($this->accounts) || $this->query == '') {
            $this->emit('selectedSubcategory', ['wiredTo' => $this->wiredTo, 'selectedSubcategory' => null]);
        } elseif ($subcategory != null) {
            $this->query = $subcategory['name'];
            $this->emit('selectedSubcategory', ['wiredTo' => $this->wiredTo, 'selectedSubcategory' => $subcategory]);
        }
        $this->openSuggestions = false;
    }

    public function mount() {
        $this->resetTo();
    }

    public function updatedQuery() {
        $this->subcategories = Subcategory::where('name', 'like', '%' . $this->query . '%');
        $this->subcategories = $this->subcategories
            ->whereHas('category', function($q) {
                $q->where('expense', $this->expenseCategories);
            })
            ->with('category')
            ->orderBy('category_id')
            ->orderBy('name')
            ->get()
            ->toArray();
        $this->openSuggestions = true;
    }

    public function render()
    {
        return view('livewire.category-auto-complete');
    }
}
