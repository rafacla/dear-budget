<div class="relative">
    <input
        type="text"
        placeholder="Account Name"
        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        autocomplete="off"
        wire:model="query"
        wire:keydown.escape="resetTo"
        wire:keydown.tab="selectItem"
        wire:keydown.enter.prevent="selectItem"
        wire:keyup.arrow-up.prevent="decrementHighlight"
        wire:keydown.arrow-down.prevent="incrementHighlight"
    />
    @if (!empty($query) && $openSuggestions)
        <div class="absolute z-10 list-group bg-white w-full rounded-t-none shadow-lg p-1">
        @if (!empty($accounts))
            @if ($hasIncomeAccounts)
                <div class="font-bold">{{__('Income Accounts')}}</div>
                @foreach ($accounts as $i => $account)
                    @if ($account['role'] == 'incomeAccount')
                    <div
                        class="pl-2 hover:bg-gray-100 cursor-pointer	 {{$highlightIndex === $i ? 'bg-gray-100' : ''}}"
                        wire:click="selectItem({{$i}})"
                    >
                        {{$account['name']}}
                    </div>
                    @endif
                @endforeach
            @endif
            @if ($hasExpenseAccounts)
                <div class="font-bold">{{__('Expense Accounts')}}</div>
                @foreach ($accounts as $i => $account)
                    @if ($account['role'] == 'expenseAccount')
                    <div
                        class="pl-2 hover:bg-gray-100 cursor-pointer	 {{$highlightIndex === $i ? 'bg-gray-100' : ''}}"
                        wire:click="selectItem({{$i}})"
                    >
                        {{$account['name']}}
                    </div>
                    @endif
                @endforeach
            @endif
            @if ($hasAssetAndLiabiliyAccounts)
                <div class="font-bold">{{__('Asset and Liability Accounts')}}</div>
                @foreach ($accounts as $i => $account)
                    @if ($account['role'] != 'incomeAccount' && $account['role'] != 'expenseAccount')
                    <div
                        class="pl-2 hover:bg-gray-100 cursor-pointer	 {{$highlightIndex === $i ? 'bg-gray-100' : ''}}"
                        wire:click="selectItem({{$i}})"
                    >
                        {{$account['name']}}
                    </div>
                    @endif
                @endforeach
            @endif
        @else
            <div>{{__('No account found.') . ' ' .  ($showIncomeAccounts ? __('It\'ll create a new Income Account') : ($showExpenseAccounts ? __('It\'ll create a new Expense Account') : ''))}}</div>
        @endif
        </div>
    @endif
</div>
