<?php

namespace App\Http\Livewire\Components;

use Livewire\Component;

class AccountAutoComplete extends Component
{
    public function render()
    {
        return <<<'blade'
        <div id="custom-search-input">
            <div class="typeahead input-group">
                <input 
                    id="search" 
                    name="search" 
                    type="text" 
                    placeholder="Search" 
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    />
            </div>
        </div>
        <script type="text/javascript">
            var route = "{{ url('account/autocomplete') }}";
            $('#search').typeahead({
                source:  function (term, process) {
                    return $.get(route, { term: term }, function (data) {
                        return process(data);
                    });
                },
                minLength: 0,
                fitToElement: true,
                showCategoryHeader: true
            });
        </script>
        <style>
            .typeahead.input-group {
                position: relative;
            }
            .typeahead.dropdown-menu {
                background-color: white;
                margin: 5px;
                padding: 5px;
                z-index: 2002;
                position: absolute;
                width: 100%;
                border-radius: 8px;
                box-shadow: 0 5px 10px rgb(0 0 0 / 20%);
            }
            .typeahead.dropdown-menu .active {
                background-color: rgb(220, 220, 220);
            }
        </style>
        blade;
    }
}
