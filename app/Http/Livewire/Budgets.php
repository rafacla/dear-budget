<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\Budget;

class Budgets extends Component
{
    public $modelClass = Budget::Class;
    public $itemClassName = 'Budget';
    public $currentDate;
    public $items;
    public $budgets = [];
    public $itemID;
    public $form = array(
        'budget_value'          => '',
        'subcategory_id'        => '',
    );
    public $isOpen = 0;

    public function render()
    {
        $this->currentDate = date('Y-m-01');
        $this->items = Auth::user()->categories->where('expense',true);
        foreach ($this->items as $item) {
            foreach ($item->subcategories as $subcategory) {
                $this->budgets[$subcategory->id] = $subcategory->budgets($this->currentDate)->first() != null ? $subcategory->budgets($this->currentDate)->first()->budget_value : 0;
            }
        }
        return view('livewire.budgets.list');
    }

    public function updatedBudgets($value, $name)
    {
        $this->modelClass::updateOrCreate(['date' => $this->currentDate, 'subcategory_id' => $name, 'user_id' => Auth::user()->id],
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

    /*
    public function edit($id)
    {
        $item = $this->modelClass::findOrFail($id);
        $this->itemID = $id;
        foreach ($this->form as $key => $value) {
            $this->form[$key] = $item[$key];
        }
    
        $this->openModal();
    }

    public function delete($id)
    {
        $this->modelClass::find($id)->delete();
        session()->flash('message', $this->itemClassName.' deleted successfully.');
    }
    */
}
