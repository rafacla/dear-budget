<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400" x-cloak>
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

        <div class="fixed inset-0 transition-opacity">
            <div class="absolute inset-0 bg-gray-500 opacity-75" wire:click="closeModal()"></div>
        </div>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all
            sm:my-8 sm:align-middle sm:max-w-full sm:w-11/12" role="dialog" aria-modal="true"
            aria-labelledby="modal-headline">
            @if ($pickTransactionId != '' && $pickTransactionId != null)
                <div
                    class="rounded bg-white shadow p-8 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                    <input type="text" wire:model="pickTransactionId" hidden>
                    <select wire:model="pickCreditCardId" class="w-full">
                        @foreach ($accountCreditCards as $item)
                            <option value="{{ $item['id'] }}">{{ $item['name'] }}</option>
                        @endforeach
                    </select>
                    <button
                        class="mt-4 inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                        wire:click="updateCreditCard()">{{ __('Save') }}</button>
                </div>
            @endif
            @if (count($pickBudgetTransactionId) > 0)
                <div
                    class="rounded bg-white shadow p-8 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                    <input type="month" wire:model="pickBudgetDate">
                    <button
                        class="mt-4 inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5"
                        wire:click="updateBudgetDate()">{{ __('Save') }}</button>
                </div>
            @endif
            <p class="font-bold px-4 py-4">{{ __('Credit Card Statement of ') }}
                <input class="font-bold border-0 px-0 w-auto" type="month" wire:model="statementBudgetDate"
                    wire:change="selectStatementBudgetDate()" id="currentDate">
            </p>
            <div class="flex">
                @php
                    $payments = 0;
                    $expenses = 0;
                    foreach ($statementTransactions as $item) {
                        if ($item['credit_account_id'] == $accountFilter) {
                            $expenses += $item['amount'];
                        }
                        if ($item['debit_account_id'] == $accountFilter) {
                            $payments += $item['amount'];
                        }
                    }
                @endphp
                <div class="w-36 flex-initial py-4 px-8 rounded bg-gray-200 mx-4 text-center align-middle">
                    <p>{{ __('Payments') }}</p>
                    <p>{{ number_format($payments, 2) }}</p>
                </div>
                <div class="my-auto text-gray-200"><i class="fas fa-2x fa-minus"></i></div>
                <div class="w-36 flex-initial py-4 px-8 rounded bg-gray-200 mx-4 text-center align-middle">
                    <p>{{ __('Expenses') }}</p>
                    <p>{{ number_format($expenses, 2) }}</p>
                </div>
                <div class="my-auto text-gray-200"><i class="fas fa-2x fa-equals"></i></div>
                <div class="w-36 flex-initial py-4 px-8 rounded bg-gray-200 mx-4 text-center align-middle">
                    @if (round($payments - $expenses, 2) == 0)
                        <p>{{ __('Paid') }}</p>
                    @elseif($payments > $expenses)
                        <p>{{ __('Overpaid') }}</p>
                        <p>+{{ number_format($payments - $expenses, 2) }}</p>
                    @else
                        <p>{{ __('Not paid') }}</p>
                        <p>{{ number_format($payments - $expenses, 2) }}</p>
                    @endif
                </div>
            </div>
            <div class="py-4">
                @if (count(array_filter($selectedCC)) > 0)
                    <div>
                        <button class="ml-4 mb-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            wire:click="changeSelectedStatementDate()">
                            <i class="far fa-calendar-alt mr-2"></i>Change Statement Date
                        </button>
                    </div>
                @endif

                @foreach ($accountCreditCards as $creditCard)
                    <div class="px-4 w-full font-bold border-b-2 border-gray-800">
                        @if ($creditCard['id'] == '' && ($creditCard['total'] ?? 0) != 0)
                            <span>{{ __('General') }}</span>
                            <span class="float-right">{{ number_format($creditCard['total'] ?? 0, 2) }}</span>
                        @elseif(($creditCard['total'] ?? 0) != 0)
                            <span>{{ $creditCard['name'] }}</span>
                            <span>({{ $creditCard['number'] }})</span>
                            <span class="float-right">{{ number_format($creditCard['total'] ?? 0, 2) }}</span>
                        @endif
                    </div>
                    <div class="w-full grid grid-cols-4 px-4">
                        @foreach ($statementTransactions as $item)
                            @if ($item['credit_card_id'] == $creditCard['id'])
                                <div class="group text-sm px-2">
                                    <input type="checkbox" wire:model="selectedCC.{{ $item['id'] }}">
                                    <span
                                        class="pr-1">{{ date('d/m', strtotime($item['transactions_journal']['date'])) }}</span>
                                    <span
                                        title="{{ $item['transactions_journal']['description'] }}">{{ mb_strimwidth($item['transactions_journal']['description'], 0, 20, '...') }}</span>
                                    <span class="float-right opacity-0 group-hover:opacity-100">
                                        <button
                                            wire:click.prevent="pickBudgetDate({{ $item['id'] }},{{ $currentDate }})"><i
                                                class="far fa-calendar-alt"></i></button>
                                        <button
                                            wire:click.prevent="pickCreditCard({{ $item['id'] }},{{ $creditCard['id'] }})"><i
                                                class="far fa-credit-card"></i></button>
                                    </span>
                                    <span class="float-right">
                                        {{ $item['amount'] }}
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>
