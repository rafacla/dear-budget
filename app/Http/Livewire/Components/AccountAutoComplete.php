<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;
use App\Models\Account;

class AccountAutoComplete extends Component
{
    public $value;
    public $assetAccounts;
    public $expenseAccounts;
    public $incomeAccounts;
    public $randomComponentId;
    public $displayExpenseAndIncomeAccounts = 'both';
    public function render()
    {
        $this->randomComponentId = uniqid();
        $this->assetAccounts = Account::where('role','checkingAccount')
                                    ->orWhere('role','walletCash')
                                    ->orWhere('role','investmentAccount')
                                    ->orWhere('role','creditCard')
                                    ->get()->toJSON();
        $this->expenseAccounts = Account::where('role','expenseAccount')->get()->toJSON();
        $this->incomeAccounts = Account::where('role','incomeAccount')->get()->toJSON();
        
        return <<<'blade'
        <div id="custom-search-input">
            <div class="input-group">
                <input 
                    type="text" 
                    placeholder="Account Name" 
                    id = "{{$this->randomComponentId}}"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    value="{{$this->value}}"
                    autocomplete="off"/>
            </div>
        </div>
        <script type="text/javascript">
            var sourceA = {!! $this->assetAccounts !!};
            var sourceE = {!! $this->expenseAccounts !!};
            var sourceI = {!! $this->incomeAccounts !!};
            var sourceA = new Bloodhound({
                datumTokenizer: function (obj) {
                    var test = Bloodhound.tokenizers.whitespace(obj.name);
                    $.each(test, function (k, v) {
                        let i = 1; // start with 1 insted of 0 because test already contains 1st value
                        while (i < v.length - 1) {
                            test.push(v.substr(i, v.length));
                            i++;
                        }
                        $.unique(test); // removes duplicate values
                    });
                    return test;
                },
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                identify: function(obj) { return obj.id; },
                local: sourceA
            });
            var displayExpenseAndIncomeAccounts = '{{$this->displayExpenseAndIncomeAccounts}}';
            var sourceE = new Bloodhound({
                datumTokenizer: function (obj) {
                    var test = Bloodhound.tokenizers.whitespace(obj.name);
                    $.each(test, function (k, v) {
                        let i = 1; // start with 1 insted of 0 because test already contains 1st value
                        while (i < v.length - 1) {
                            test.push(v.substr(i, v.length));
                            i++;
                        }
                        $.unique(test); // removes duplicate values
                    });
                    return test;
                },
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                identify: function(obj) { return obj.id; },
                local: (displayExpenseAndIncomeAccounts == 'both' || displayExpenseAndIncomeAccounts == 'expense') ? sourceE : []
            });
            var sourceI = new Bloodhound({
                datumTokenizer: function (obj) {
                    var test = Bloodhound.tokenizers.whitespace(obj.name);
                    $.each(test, function (k, v) {
                        let i = 1; // start with 1 insted of 0 because test already contains 1st value
                        while (i < v.length - 1) {
                            test.push(v.substr(i, v.length));
                            i++;
                        }
                        $.unique(test); // removes duplicate values
                    });
                    return test;
                },
                queryTokenizer: Bloodhound.tokenizers.whitespace,
                identify: function(obj) { return obj.id; },
                local: (displayExpenseAndIncomeAccounts == 'both' || displayExpenseAndIncomeAccounts == 'income') ? sourceI : []
            });
            
            var expenseAccount = {
                    name: 'expenseAccount',
                    source: sourceE,
                    display: 'name',
                    templates: {
                        header: '<strong>{{__('Expense Accounts')}}</strong>',
                    }
                };
            var incomeAccount = {
                    name: 'incomeAccount',
                    source: sourceI,
                    display: 'name',
                    templates: {
                        header: '<strong>{{__('Income Accounts')}}</strong>',
                    }
                };
            var notFoundTemplate = (displayExpenseAndIncomeAccounts == 'both') ? '{{__('No account found')}}' : '{{__('No account found, this will create a new one...')}}';
            $('#{{$this->randomComponentId}}').typeahead({
                hint: true,
                highlight: true,
                minLength: 1
            },
            {
                name: 'checkingAccount',
                source: sourceA,
                display: 'name',
                templates: {
                    header: '<strong>{{__('Asset Accounts')}}</strong>',
                    notFound: notFoundTemplate
                }
            },
            expenseAccount,
            incomeAccount
        ).on('typeahead:render', function(element, objs, async, name) {
            if($('.tt-suggestion').length){
                $('.empty-message').hide();
            } else {
                $('.empty-message').show();
            }
        }).on('typeahead:select typeahead:autocomplete', function(event, object) {
            console.log(object);    
        });
        
        
        </script>
        blade;
    }
}
