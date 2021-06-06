<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{__('Manage Budgets')}}
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex">
            <div class="flex divide-x divide-white text-center -mt-8 mb-5 py-4 px-3 rounded-lg {{$toBudget > 0 ? 'bg-green-500 text-white' : ($toBudget < 0 ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700')}}">
                <div class="px-2">
                    <p class="text-3xl">{{number_format($toBudget,2,'.',',')}}</p>
                    <p class="text-green-900">{{__('To be budgeted')}}</p>
                </div>
                <div class="px-2 text-gray-900">
                    <table>
                        <tr>
                            <td class="py-0 text-sm">{{number_format($incomeMonth,2,".",",")}}</td>
                            <td class="py-0 text-sm text-left italic">{{__('Income this Month')}}</td>
                        </tr>
                        <tr>
                            <td class="py-0 text-sm">{{number_format($overspentLMonth,2,".",",")}}</td>
                            <td class="py-0 text-sm text-left italic">{{__('Overspent last Month')}}</td>
                        </tr>
                        <tr>
                            <td class="py-0 text-sm">{{number_format($budgetedMonth,2,".",",")}}</td>
                            <td class="py-0 text-sm text-left italic">{{__('Budgeted this Month')}}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="w-full -mt-12 text-right">
            <a href="{{route('budget.date',['year' => $currentDate->format('Y'), 'month'=>($currentDate->format('m')-1)])}}">
                <i class="text-gray-700 text-2xl far fa-arrow-alt-circle-left"></i>
            </a>
            <input class="text-gray-700 text-xl w-36 outline-none bg-transparent border-none" type="text" value="{{$currentDate->format('M-Y')}}" disabled>
            <a href="{{route('budget.date',['year' => $currentDate->format('Y'), 'month'=>($currentDate->format('m')+1)])}}">
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
                        <th class="px-2 py-1 text-sm text-left">{{__('Name')}}</th>
                        <th class="px-4 py-1 w-36 text-sm">{{__('Budgeted')}}</th>
                        <th class="px-2 py-1 w-36 text-sm">{{__('Transactions')}}</th>
                        <th class="px-2 py-1 w-36 text-sm">{{__('Available')}}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($items as $item)
                    <?php
                        $sumOfSubcategoriesBudget = 0;
                        foreach ($item->subcategories as $value) {
                            $sumOfSubcategoriesBudget += $budgets[$value->id];
                        }
                    ?>
                    <tr class="border bg-blue-50">
                        <td class="px-2 py-1 text-sm font-bold">{{ $item->name }}</td>
                        <td class="px-4 py-1 text-sm font-bold">
                            <input type="number" step=".01" value="{{number_format($sumOfSubcategoriesBudget,2,".","")}}"
                                    class="appearance-none border-none rounded w-full px-3 text-blue-800 bg-blue-50 text-right leading-tight focus:outline-none focus:shadow-outline" disabled>
                        </td>
                        <td class="px-2 py-1 text-sm font-bold">

                        </td>
                        <td class="px-2 py-1 text-sm font-bold">

                        </td>
                    </tr>
                        @foreach($item->subcategories as $subcategory)
                        <tr class="border">
                            <td class="px-8 py-1 text-sm"> {{ $subcategory->name }}</td>
                            <td class="px-4 py-1 text-sm">
                                <input type="number" step=".01" wire:model.lazy="budgets.{{$subcategory->id}}"
                                    class="{{$budgets[$subcategory->id] == 0 ? 'text-gray-200' : 'text-gray-700'}} focus:text-blue-700 appearance-none border-none rounded w-full px-3 text-right leading-tight focus:outline-none focus:shadow-outline">
                            </td>
                            <td class="px-2 py-1 text-sm" style="text-align: right;">
                                {{number_format($transactions[$subcategory->id] ?? 0,2)}}
                            </td>
                            <td class="px-2 py-1 text-sm" style="text-align: right;">
                                <div class="inline-block px-2 py-1 rounded-full text-sm  {{$available[$subcategory->id] > 0 ? 'bg-green-500 text-white' : ($available[$subcategory->id] < 0 ? 'bg-red-500 text-white' : 'bg-gray-300 text-gray-700')}}">
                                    {{$available[$subcategory->id]}}
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
