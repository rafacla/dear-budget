<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400" x-cloak>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-full sm:w-11/12"
            role="dialog" aria-modal="true" aria-labelledby="modal-headline">
            <form>
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="flex flex-wrap -mx-2 overflow-hidden">
                        <div class="w-1/4">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('Date') }}:</label>
                                <input type="date"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    placeholder="{{ __('Date') }}" wire:model="form.date">
                                @error('form.date') <span class="text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div class="mb-4">
                                <label
                                    class="block text-gray-700 text-sm font-bold mb-2">{{ __('Description') }}:</label>
                                <input type="text"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    placeholder="{{ __('Description') }}" wire:model="form.description">
                                @error('form.description') <span
                                    class="text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div class="mb-4">
                                <label
                                    class="block text-gray-700 text-sm font-bold mb-2">{{ __('Transaction Number') }}:</label>
                                <input type="text"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    placeholder="{{ __('Transaction Number') }}" wire:model="form.transaction_number">
                                @error('form.transaction_number') <span
                                    class="text-red-500">{{ $message }}</span>@enderror
                            </div>
                            <div class="mb-4">
                                <label
                                    @popper({{ __('Use this field whenever a transaction is meant to be taken in budget in a different month than when it happened') }})
                                    class="block text-gray-700 text-sm font-bold mb-2 underline">{{ __('Budget Date') }}:
                                    ❔</label>
                                <input type="date"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                    placeholder="{{ __('Budget Date') }}" wire:model="form.budget_date">
                                @error('form.budget_date') <span
                                    class="text-red-500">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="w-3/4 px-2 divide-y">
                            <label class="block text-gray-700 text-sm font-bold mb-2">{{ __('Transactions') }}:</label>

                            <table class="w-full">
                                <thead>
                                    <th>{{ __('Source Account') }}</th>
                                    <th>{{ __('Destination Account') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    @php
                                        $i = 0;
                                    @endphp
                                    @foreach ($form['transactions'] as $key => $item)
                                        <tr>
                                            <td>
                                                @livewire('account-auto-complete',
                                                [
                                                'wiredTo' => 'transactions-'.$key.'-credit_account',
                                                'initialQuery' => ($item != null && $item['credit_account'] != null) ?
                                                $item['credit_account']['name'] : '',
                                                'showExpenseAccounts' => false
                                                ],
                                                key('transactions-'.$key.'-credit_account')
                                                )
                                            </td>
                                            <td>
                                                @livewire('account-auto-complete',
                                                [
                                                'wiredTo' => 'transactions-'.$key.'-debit_account',
                                                'initialQuery' => ($item != null && $item['debit_account'] != null) ?
                                                $item['debit_account']['name'] : '',
                                                'showIncomeAccounts' => false
                                                ],
                                                key('transactions-'.$key.'-debit_account')
                                                )
                                            </td>
                                            <td>
                                                <input
                                                    class="text-right shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                    type="number" step="0.01"
                                                    wire:model="form.transactions.{{ $key }}.amount">
                                            </td>
                                            <td>
                                                @php
                                                    $expenseCategories = true;
                                                    if ($item['credit_account'] != null) {
                                                        if ($item['credit_account']['role'] == 'incomeAccount') {
                                                            $expenseCategories = false;
                                                        }
                                                    } else {
                                                        if ($item['credit_account_name'] != '' && $item['credit_account_name'] != null) {
                                                            $expenseCategories = false;
                                                        }
                                                    }
                                                @endphp
                                                @if ($item != null && $item['credit_account'] != null && $item['debit_account'] != null && $item['credit_account']['role'] != 'incomeAccount' && $item['debit_account']['role'] != 'expenseAccount')
                                                    <!-- this is a Transfer -->
                                                    <input
                                                        class="cursor-not-allowed bg-gray-50 shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                                        type="text" value="{{ __('Transfer') }}" disabled>
                                                @else
                                                    <!-- this is an Expense or an Income -->
                                                    @if ($expenseCategories)
                                                    @livewire('category-auto-complete',
                                                    [
                                                    'wiredTo' => 'transactions-'.$key.'-subcategory',
                                                    'initialQuery' => (($item != null && $item['subcategory'] != null) ?
                                                                    $item['subcategory']['name']: ''),
                                                    'expenseCategories' => true
                                                    ],
                                                    key('transactions-'.$key.'-subcategory-debit')
                                                    )
                                                    @else
                                                    @livewire('category-auto-complete',
                                                    [
                                                    'wiredTo' => 'transactions-'.$key.'-subcategory',
                                                    'initialQuery' => (($item != null && $item['subcategory'] != null) ?
                                                                    $item['subcategory']['name']: ''),
                                                    'expenseCategories' => false
                                                    ],
                                                    key('transactions-'.$key.'-subcategory-credit')
                                                    )
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @if ($i > 0)
                                                    <button
                                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                                        wire:click.prevent="deleteTransaction({{ $key }})">
                                                        <i class="fas fa-minus-circle"></i>
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                        @php
                                            $i++;
                                        @endphp
                                    @endforeach
                                </tbody>
                            </table>
                            <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                wire:click.prevent="addTransaction()">
                                <i class="fas fa-plus-circle"></i> {{ __('Add Split Transaction')}}
                            </button>
                            @if ($transactionsValidation != null)
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-2" role="alert">{{$transactionsValidation}}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
                        <button wire:click.prevent="store()" type="button"
                            class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                            {{ __('Save') }}
                        </button>
                    </span>
                    <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">

                        <button @click="isOpen = false" type="button"
                            class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
                            {{ __('Cancel') }}
                        </button>
                    </span>
            </form>
        </div>

    </div>
</div>
