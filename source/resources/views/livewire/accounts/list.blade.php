<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
        {{__('Manage Accounts')}}
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4 dark:bg-gray-600">
            @if (session()->has('message'))
                <div class="bg-green-500 border-t-4 border-green-600 rounded-b text-green-900 px-4 py-3 shadow-md my-3" role="alert">
                  <div class="flex">
                    <div>
                      <p class="text-sm">{{ session('message') }}</p>
                    </div>
                  </div>
                </div>
            @endif
            <div class="grid grid-flow-col auto-cols-max">
                <div class="self-center">
                    <button wire:click="create()" class="dark:bg-green-500 dark:hover:bg-green-700 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">{{__('Create New Account')}}</button>
                </div>
                <div class="self-center mx-2">
                    <select class="dark:bg-gray-300 shadow appearance-none border rounded w-full py-2 pr-8 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1"  wire:model="accountFilter">
                        <option value="assetLiabilityAccount">{{__('Asset and Liability accounts')}}</option>
                        <option value="assetAccount">{{__('Asset accounts')}}</option>
                        <option value="liabilityAccount">{{__('Liability accounts')}}</option>
                        <option value="expenseAccount">{{__('Expense accounts')}}</option>
                        <option value="incomeAccount">{{__('Income accounts')}}</option>
                    </select>
                </div>
            </div>
            <div x-data="{ isOpen: @entangle('isOpen') }">
                <div x-show.transition="isOpen === true" style="display:none">
                    @include('livewire.accounts.create')
                </div>
            </div>
            <div x-data="{ creditCardModal: @entangle('creditCardModal') }">
                <div x-show.transition="creditCardModal === true" style="display:none">
                    @include('livewire.accounts.credit_card')
                </div>
            </div>
            <table class="table-auto w-full">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                        <th class="px-4 py-2 text-sm">{{__('Name')}}</th>
                        <th class="px-4 py-2 text-sm">{{__('Account Role')}}</th>
                        <th class="px-2 py-2 text-sm">{{__('Account Number')}}</th>
                        <th class="px-4 py-2 text-sm">{{__('Current Balance')}}</th>
                        <th class="px-4 py-2 text-sm">{{__('Last Transaction Date')}}</th>
                        <th class="px-4 py-2 text-sm"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr class="dark:text-gray-200 group">
                        <td class="px-4 py-0.5 text-sm group-hover:underline">
                            <a href="{{route('transaction.account',['accountID' => $item->id])}}">
                                {{ $item->name }}
                                <i class="fas fa-external-link-alt ml-2 opacity-0 group-hover:opacity-100"></i>
                            </a>
                        </td>
                        <td class="px-4 py-0.5 text-sm">{{ __($accountRoles[$item->role]['name']) }}</td>
                        <td class="px-2 py-0.5 text-sm">{{ $item->number }}</td>
                        <td class="px-4 py-0.5 text-sm text-right">{{ number_format($item->balance(), 2) }}</td>
                        <td class="px-4 py-0.5 text-sm text-center">{{ $item->lastTransactionDate() }}</td>
                        <td class="px-4 py-0.5 text-sm" style="text-align: right;">
                            @if ($item->role == 'creditCard')
                            <button wire:click="cards({{ $item->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-0.5 px-1 rounded">{{__('Cards')}}</button>
                            @endif
                            <button wire:click="edit({{ $item->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-0.5 px-1 rounded">{{__('Edit')}}</button>
                            <button wire:click="delete({{ $item->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-0.5 px-1 rounded">{{__('Delete')}}</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
