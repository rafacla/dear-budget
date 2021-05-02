<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{__('Manage Transactions')}}
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="w-full -mt-12 text-right">
            <a href="{{route('transaction.date',['year' => date("Y",$currentDate), 'month'=>(date("m",$currentDate)-1)])}}">
                <i class="text-gray-700 text-2xl far fa-arrow-alt-circle-left"></i>
            </a>
            <input class="text-gray-700 text-xl w-36 outline-none bg-transparent border-none" type="text" value="{{date('M-Y',$currentDate)}}" disabled>
            <a href="{{route('transaction.date',['year' => date("Y",$currentDate), 'month'=>(date("m",$currentDate)+1)])}}">
                <i class="text-gray-700 text-2xl far fa-arrow-alt-circle-right"></i>
            </a>
        </div>
        <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 py-4">
            @if (session()->has('message'))
                <div class="bg-teal-100 border-t-4 border-teal-500 rounded-b text-teal-900 px-4 py-3 shadow-md my-3" role="alert">
                  <div class="flex">
                    <div>
                      <p class="text-sm">{{ session('message') }}</p>
                    </div>
                  </div>
                </div>
            @endif
            <table class="table-fixed w-full">
                <thead>
                    <tr class="border">
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
                    @foreach($items as $item)
                    @if (sizeof($item->transactions)>0)
                    <tr class="border">
                        <td class="px-1 py-0.5 text-xs"><input type="checkbox" wire:model="selected.{{$item->id}}"></td>
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
                            <span @popper(
                                {{sizeof($item->transactions) == 1 ? 
                                    __($accountRoles[$item->transactions->first()->creditAccount->role]['name']) :
                                    __('This transaction has been splitted, it has more than one account assigned.')
                                }})>
                                {{sizeof($item->transactions) == 1 ? 
                                    ($accountRoles[$item->transactions->first()->creditAccount->role]['icon'] . $item->transactions->first()->creditAccount->name) : 
                                    __('Multiple Accounts')}}
                            </span>
                        </td>
                        <td class="px-2 py-1 text-xs">
                            <span @popper(
                                {{sizeof($item->transactions) == 1 ? 
                                    __($accountRoles[$item->transactions->first()->debitAccount->role]['name']) :
                                    __('This transaction has been splitted, it has more than one account assigned.')
                                }})>
                                {{sizeof($item->transactions) == 1 ? 
                                ($accountRoles[$item->transactions->first()->debitAccount->role]['icon'] . $item->transactions->first()->debitAccount->name) : 
                                __('Multiple Accounts')}}
                            </span>
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
                    @if (sizeof($items)==0)
                    <tr class="border">
                        <td colspan="6" class="px-2">
                            {{__('No items found to show')}}
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    $(function() {
        $('#datepicker').datepicker( {
            changeMonth: true,
            changeYear: true,
            showButtonPanel: true,
            dateFormat: 'MM yy',
            onClose: function(dateText, inst) { 
                $(this).datepicker('setDate', new Date(inst.selectedYear, inst.selectedMonth, 1));
            }
        });
    });
</script>