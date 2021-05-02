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
                        <th class="px-2 py-1 w-24 text-sm text-left">{{__('Date')}}</th>
                        <th class="px-4 py-1 w-48 text-sm text-left">{{__('Description')}}</th>
                        <th class="px-2 py-1 w-36 text-sm">{{__('Category')}}</th>
                        <th class="px-2 py-1 w-48 text-sm">{{__('Source Account')}}</th>
                        <th class="px-2 py-1 w-48 text-sm">{{__('Destination Account')}}</th>
                        <th class="px-2 py-1 w-36 text-sm">{{__('Amount')}}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr class="border">
                        <td class="px-2 py-1 text-sm">{{ $item->date }}</td>
                        <td class="px-4 py-1 text-sm">{{ $item->description }}</td>
                        <td class="px-2 py-1 text-sm">
                            
                        </td>
                        <td class="px-2 py-1 text-sm">
                            <span @popper({{__($accountRoles[$item->transactions->first()->creditAccount->role]['name'])}})>
                                {{sizeof($item->transactions) == 1 ? 
                                    ($accountRoles[$item->transactions->first()->creditAccount->role]['icon'] . $item->transactions->first()->creditAccount->name) : 
                                    'Multiple Accounts'}}
                            </span>
                        </td>
                        <td class="px-2 py-1 text-sm">
                            <span @popper({{__($accountRoles[$item->transactions->first()->debitAccount->role]['name'])}})>
                                {{sizeof($item->transactions) == 1 ? 
                                ($accountRoles[$item->transactions->first()->debitAccount->role]['icon'] . $item->transactions->first()->debitAccount->name) : 
                                'Multiple Accounts'}}
                            </span>
                        </td>
                        <td class="px-2 py-1 text-sm">
                            
                        </td>
                    </tr>
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