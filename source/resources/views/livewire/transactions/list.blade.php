<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
        {{__('Manage Transactions')}}
    </h2>
</x-slot>
<div  x-data="{ isOpen: @entangle('isOpen') }">
    <div x-show.transition="isOpen" style="display:none">
        @include('livewire.transactions.create')
    </div>
</div>
<div x-data="{ isStatementOpen: @entangle('isStatementOpen') }">
    <div x-show.transition="isStatementOpen" style="display:none">
        @include('livewire.transactions.cc_statement')
    </div>
</div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="self-center">
            <button wire:click="new()"
                class="dark:bg-green-500 dark:hover:bg-green-700 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">
                <i class="far fa-plus-square"></i> {{__('Create New Transaction')}}
            </button>
            <select 
                class="dark:bg-gray-300 shadow appearance-none border rounded py-2 pr-8 w-auto text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                wire:change="openBalance($event.target.value)" wire:model="accountFilter">
                <option value="">{{__('All Accounts')}}</option>
                @foreach (json_decode($assetAccounts) as $item)
                    <option value="{{$item->id}}">{{$item->name}}</option>
                @endforeach
            </select>
            @if ($accountFilterRole == 'creditCard')
                <button class="dark:bg-yellow-300 dark:hover:bg-yellow-400 bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-2 rounded inline-flex items-center"
                    wire:click="showStatement({{$accountFilter}})">
                    <i class="far fa-file-alt px-1"></i> {{__('Credit Card Statement')}}
                </button>
            @endif
            @if (count(array_filter($selected, function($item) {
                return $item;
            })) > 0)
            <button wire:click="deleteSelected()"
                class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded my-3">
                <i class="far fa-minus-square"></i> {{__('Delete Selected Transactions')}}
            </button>
            @endif
        </div>
        <div class="w-full -mt-12 text-right">
            <a href="{{
                    $accountFilter != '' ? 
                        route('transaction.account.date',['accountID' => $accountFilter, 'year' => date("Y",$currentDate), 'month'=>(date("m",$currentDate)-1)])
                        : route('transaction.date',['year' => date("Y",$currentDate), 'month'=>(date("m",$currentDate)-1)])
                }}">
                <i class="dark:text-gray-800 dark:hover:text-gray-200 hover:text-gray-900 text-gray-700 text-2xl far fa-arrow-alt-circle-left"></i>
            </a>
            <input class="dark:text-gray-800 text-gray-700 text-xl w-36 outline-none bg-transparent border-none" type="text" value="{{date('M-Y',$currentDate)}}" disabled>
            <a href="{{
                    $accountFilter != '' ?
                    route('transaction.account.date',['accountID' => $accountFilter, 'year' => date("Y",$currentDate), 'month'=>(date("m",$currentDate)+1)])
                    : route('transaction.date',['year' => date("Y",$currentDate), 'month'=>(date("m",$currentDate)+1)])
                }}">
                <i class="dark:text-gray-800 dark:hover:text-gray-200 hover:text-gray-900 text-gray-700 text-2xl far fa-arrow-alt-circle-right"></i>
            </a>
        </div>
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
            @if ($accountFilter == null || $accountFilter == '')
            <table class="table-fixed w-full">
                <thead>
                    <tr class="border-b border-t dark:border-gray-800 dark:text-gray-100 dark:bg-gray-500">
                        <th class="px-1 py-0.5 w-4 text-xs"><input type="checkbox" wire:model="selectedAll"></th>
                        <th class="px-2 py-1 w-24 text-xs text-left">{{__('Date')}}</th>
                        <th class="px-4 py-1  text-xs text-left">{{__('Description')}}</th>
                        <th class="px-2 py-1 w-56 text-xs text-left">{{__('Category')}}</th>
                        <th class="px-2 py-1 w-36 text-xs text-left">{{__('Source Account')}}</th>
                        <th class="px-2 py-1 w-36 text-xs text-left">{{__('Destination Account')}}</th>
                        <th class="px-2 py-1 w-36 text-xs">{{__('Amount')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $noTransactions = true;
                    @endphp
                    @foreach($items as $item)
                    @if (sizeof($item->transactions)>0 && $item->deleted_at == null
                        && ($transactionTypes[$item->transactions->first()->type]['type']!='initialBalance'
                            || ($item->transactions->first()->debitAccount != null
                                && ($item->transactions->first()->debitAccount->role != 'incomeAccount'
                                    && $item->transactions->first()->debitAccount->role != 'expenseAccount')
                                )
                            )
                        )
                    @php

                        $noTransactions = false;
                    @endphp
                    <tr
                        class="dark:hover:bg-green-500 border-b hover:bg-blue-50 dark:border-gray-800 dark:text-gray-100
                            {{ ($transactionTypes[$item->transactions->first()->type]['type']=='initialBalance') ? 'cursor-not-allowed' : 'cursor-pointer' }}
                        "
                        @if($transactionTypes[$item->transactions->first()->type]['type']=='initialBalance')
                            {{Popper::delay(500,0)->pop(__('To edit this initial balance transaction, please edit the account.'))}}
                        @else
                            wire:click="edit({{ $item->id }})")
                        @endif
                        >
                        <td class="px-1 py-0.5 text-xs" onclick="event.cancelBubble=true;"><input type="checkbox" wire:model="selected.{{$item->id}}"></td>
                        <td class="px-2 py-1 text-xs">{{ $item->date }}</td>
                        <td class="px-4 py-1 text-xs">{{ $item->description }}</td>
                        <td class="px-2 py-1 text-xs">
                            @if (sizeof($item->transactions) > 1)
                            <span>{{__('Multiple Items')}}</span>
                            @else
                                @if ($transactionTypes[$item->transactions->first()->type]['type']=='transfer')
                                <span class="bg-gray-400 text-white rounded-full text-xs py-0.5 px-2"
                                    @popper({{__('This is a Transfer! As you are just moving funds between accounts, it doesn\'t affect your budget!')}})
                                >
                                    <i class="fas fa-exchange-alt"></i> {{__('Transfer')}}
                                </span>
                                @elseif($transactionTypes[$item->transactions->first()->type]['type']=='initialBalance')
                                <span class="bg-gray-400 text-white rounded-full text-xs py-0.5 px-2"
                                @popper({{__('This is the initial transaction of the destination account, you don\'t have to pick a category for this.')}})
                                >
                                    <i class="fas fa-certificate"></i> {{__('Opening Balance')}}
                                </span>
                                @else
                                    @if ($item->transactions->first()->subcategory==null)
                                        <span class="bg-red-500 text-white rounded-full text-xs py-0.5 px-2"
                                            @popper({{__('For any expense transaction, you have to pick a category, otherwise your budget will not be accurate!')}})
                                        >
                                            <i class="fas fa-exclamation-triangle"></i> {{__('No category defined!')}}
                                        </span>
                                    @else
                                        <span class="text-xs py-1">
                                            {{$item->transactions->first()->subcategory->name}}
                                        </span>
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td class="px-2 py-1 text-xs">
                            @if ($item->transactions->first()->creditAccount != null)
                            <span @popper(
                                {{sizeof($item->transactions) == 1 ?
                                    __($accountRoles[$item->transactions->first()->creditAccount->role]['name']) :
                                    __('This transaction has been splitted, it has more than one account assigned.')
                                }})>
                                {{sizeof($item->transactions) == 1 ?
                                    ($accountRoles[$item->transactions->first()->creditAccount->role]['icon'] . $item->transactions->first()->creditAccount->name) :
                                    __('Multiple Accounts')}}
                            </span>
                            @endif
                        </td>
                        <td class="px-2 py-1 text-xs">
                            @if ($item->transactions->first()->debitAccount != null)
                            <span @popper(
                                {{sizeof($item->transactions) == 1 ?
                                    __($accountRoles[$item->transactions->first()->debitAccount->role]['name']) :
                                    __('This transaction has been splitted, it has more than one account assigned.')
                                }})>
                                {{sizeof($item->transactions) == 1 ?
                                ($accountRoles[$item->transactions->first()->debitAccount->role]['icon'] . $item->transactions->first()->debitAccount->name) :
                                __('Multiple Accounts')}}
                            </span>
                            @endif
                        </td>
                        <td class="px-2 py-1 text-xs text-right">
                            <?php
                                $sumAmount =0;
                                foreach ($item->transactions as $transaction) {
                                    $sumAmount += $transaction->amount;
                                }
                            ?>
                            {{number_format($sumAmount,2)}}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                    @if ($noTransactions)
                    <tr class="border">
                        <td colspan="7" class="px-2">
                            {{__('No items found to show')}}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
            @else
            <table class="table-fixed w-full">
                <thead>
                    <tr class="border-b border-t dark:border-gray-800 dark:text-gray-100 dark:bg-gray-500">
                        <th class="px-1 py-0.5 w-4 text-xs"><input type="checkbox" wire:model="selectedAll"></th>
                        <th class="px-2 py-1 w-24 text-xs text-left">{{__('Date')}}</th>
                        <th class="px-4 py-1  text-xs text-left">{{__('Description')}}</th>
                        <th class="px-2 py-1 w-56 text-xs text-left">{{__('Category')}}</th>
                        <th class="px-2 py-1 w-36 text-xs">{{__('Amount')}}</th>
                        <th class="px-2 py-1 w-36 text-xs">{{__('Balance')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $noTransactions = true;
                        $runningBalance = $cumulativeBalanceLastMonth;
                    @endphp
                    <tr class="dark:hover:bg-green-500 border-b hover:bg-blue-50 dark:border-gray-800 dark:text-gray-100 cursor-not-allowed"
                        {{Popper::delay(500,0)->pop(__('Last Month balance of selected account'))}}>
                        <td class="px-1 py-0.5 text-xs" onclick="event.cancelBubble=true;"><input type="checkbox" disabled class="cursor-not-allowed"></td>
                        <td></td>
                        <td class="px-4 py-1 text-xs">{{__('Last Month balance of selected account')}}</td>
                        <td colspan="2"></td>
                        <td class="px-2 py-1 text-xs text-right">
                            {{number_format($runningBalance,2)}}
                        </td>
                    </tr>
                    @foreach($items as $item)
                    @if (sizeof($item->transactions)>0 && $item->deleted_at == null
                        && ($transactionTypes[$item->transactions->first()->type]['type']!='initialBalance'
                            || ($item->transactions->first()->debitAccount != null
                                && ($item->transactions->first()->debitAccount->role != 'incomeAccount'
                                    && $item->transactions->first()->debitAccount->role != 'expenseAccount')
                                )
                            )
                        )
                    <tr
                        class="dark:hover:bg-green-500 border-b hover:bg-blue-50 dark:border-gray-800 dark:text-gray-100
                            {{ ($transactionTypes[$item->transactions->first()->type]['type']=='initialBalance') ? 'cursor-not-allowed' : 'cursor-pointer' }}
                        "
                        @if($transactionTypes[$item->transactions->first()->type]['type']=='initialBalance')
                            {{Popper::delay(500,0)->pop(__('To edit this initial balance transaction, please edit the account.'))}}
                        @else
                            wire:click="edit({{ $item->id }})")
                        @endif
                        >
                        <td class="px-1 py-0.5 text-xs" onclick="event.cancelBubble=true;"><input type="checkbox" wire:model="selected.{{$item->id}}"></td>
                        <td class="px-2 py-1 text-xs">{{ $item->date }}</td>
                        <td class="px-4 py-1 text-xs">{{ $item->description }}</td>
                        <td class="px-2 py-1 text-xs">
                            @if (sizeof($item->transactions) > 1)
                            <span>{{__('Multiple Items')}}</span>
                            @else
                                @if ($transactionTypes[$item->transactions->first()->type]['type']=='transfer')
                                <span class="bg-gray-400 text-white rounded-full text-xs py-0.5 px-2"
                                    @popper({{__('This is a Transfer! As you are just moving funds between accounts, it doesn\'t affect your budget!')}})
                                >
                                    <i class="fas fa-exchange-alt"></i> {{__('Transfer')}}
                                </span>
                                @elseif($transactionTypes[$item->transactions->first()->type]['type']=='initialBalance')
                                <span class="bg-gray-400 text-white rounded-full text-xs py-0.5 px-2"
                                @popper({{__('This is the initial transaction of the destination account, you don\'t have to pick a category for this.')}})
                                >
                                    <i class="fas fa-certificate"></i> {{__('Opening Balance')}}
                                </span>
                                @else
                                    @if ($item->transactions->first()->subcategory==null)
                                        <span class="bg-red-500 text-white rounded-full text-xs py-0.5 px-2"
                                            @popper({{__('For any expense transaction, you have to pick a category, otherwise your budget will not be accurate!')}})
                                        >
                                            <i class="fas fa-exclamation-triangle"></i> {{__('No category defined!')}}
                                        </span>
                                    @else
                                        <span class="text-xs py-1">
                                            {{$item->transactions->first()->subcategory->name}}
                                        </span>
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td class="px-2 py-1 text-xs text-right">
                            <?php
                                $sumAmount =0;
                                foreach ($item->transactions as $transaction) {
                                    if ($transaction->creditAccount != null && $transaction->creditAccount->id == $accountFilter) {
                                        $sumAmount -= $transaction->amount;
                                    } elseif ($transaction->debitAccount != null && $transaction->debitAccount->id == $accountFilter) {
                                        $sumAmount += $transaction->amount;
                                    }
                                }
                                $runningBalance += $sumAmount;
                            ?>
                            {{number_format($sumAmount,2)}}
                        </td>
                        <td class="px-2 py-1 text-xs text-right">
                            {{number_format($runningBalance,2)}}
                        </td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
