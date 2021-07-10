<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
        {{__('Manage Budgets')}}
    </h2>
</x-slot>
<div class="pt-12 max-w-7xl mx-auto sm:px-6 lg:px-8 h-full flex flex-col">
    <div class="flex-none">
        <div class="flex">
            <div class="flex divide-x divide-white text-center -mt-8 mb-5 py-4 px-3 rounded-lg {{$toBudget > 0 ? 'bg-green-500 text-white' : ($toBudget < 0 ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700')}}">
                <div class="px-2 justify-center hfull m-auto">
                    <p class="text-3xl">{{number_format($toBudget,2,'.',',')}}</p>
                    <p class="text-gray-900 italic">{{__('(to be budgeted)')}}</p>
                </div>
                <div class="px-2 text-gray-900">
                    <table>
                        <tr>
                            <td class="py-0 text-sm text-right {{round($toBudget,2) <> 0 ? 'text-white' : ''}}">{{number_format($incomeMonth,2,".",",")}}</td>
                            <td class="px-1 py-0 text-sm text-left italic">{{__('Income this Month')}}</td>
                        </tr>
                        <tr>
                            <td class="py-0 text-sm text-right {{round($toBudget,2) <> 0 ? 'text-white' : ''}}">{{number_format($overspentLMonth,2,".",",")}}</td>
                            <td class="px-1 py-0 text-sm text-left italic">{{__('Overspent last Month')}}</td>
                        </tr>
                        <tr>
                            <td class="py-0 text-sm text-right {{round($toBudget,2) <> 0 ? 'text-white' : ''}}">{{number_format($budgetedMonth,2,".",",")}}</td>
                            <td class="px-1 py-0 text-sm text-left italic">{{__('Budgeted this Month')}}</td>
                        </tr>
                        <tr>
                            <td class="py-0 text-sm text-right {{round($toBudget,2) <> 0 ? 'text-white' : ''}}">{{number_format(abs($transferredFromToInvestmentAccount),2,".",",")}}</td>
                            <td class="px-1 py-0 text-sm text-left italic">{{$transferredFromToInvestmentAccount > 0 ? __('Transfer from Investment') : __('Transfer to Investment')}}</td>
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
        @if (($available[null] ?? false) != null)
        <div class="flex-none bg-red-100 border-t-4 border-red-500 rounded-b text-red-900 px-4 py-3 shadow-md my-3" role="alert">
            <div class="flex">
                <div>
                <p class="text-sm">{{__('There are expenses without categories. This may mess your budget. Fix it.') }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>
    <div class="flex-1 bg-white overflow-hidden shadow-xl sm:rounded-lg px-4 pt-4 dark:bg-gray-600">
        <div class="h-full overflow-y-auto">
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
                    <tr class="">
                        <th class="sticky top-0 border-b border-t px-2 py-1 text-sm text-left dark:text-gray-200 dark:border-gray-800 dark:bg-gray-500">{{__('Name')}}</th>
                        <th class="sticky top-0 border-b border-t px-4 py-1 w-36 text-sm dark:text-gray-200 dark:border-gray-800 dark:bg-gray-500">{{__('Budgeted')}}</th>
                        <th class="sticky top-0 border-b border-t px-2 py-1 w-36 text-sm dark:text-gray-200 dark:border-gray-800 dark:bg-gray-500">{{__('Transactions')}}</th>
                        <th class="sticky top-0 border-b border-t px-2 py-1 w-36 text-sm dark:text-gray-200 dark:border-gray-800 dark:bg-gray-500">{{__('Available')}}</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- First we show investment header, then the rest -->
                    <tr class="border-b py-16 dark:text-gray-200 dark:border-gray-800 dark:bg-green-700">
                        <td class="px-2 py-1 text-sm font-bold"> {{ __('Investments') }}</td>
                        <td class="px-4 py-1 text-sm"></td>
                        <td class="px-2 py-1 text-sm" style="text-align: right;">
                            {{number_format($investmentMonth,2,".",",")}}
                        </td>
                        <td class="px-2 py-1 text-sm" style="text-align: right;">
                            <div class="inline-block px-2 py-1 rounded-full text-sm  {{(round($investmentCumulative,2) ?? 0) > 0 ? 'bg-green-500 text-white' : ((round($investmentCumulative,2) ?? 0) < 0 ? 'bg-red-500 text-white' : 'bg-gray-300 text-gray-700')}}">
                                {{number_format($investmentCumulative ?? 0,2)}}
                            </div>
                        </td>
                    </tr>
                    @foreach($items as $item)
                    <?php
                        $sumOfSubcategoriesBudget = 0;
                        $sumOfSubcategoriesTransactions = 0;
                        $sumOfSubcategoriesAvailable = 0;
                        foreach ($item->subcategories as $value) {
                            $sumOfSubcategoriesBudget += $budgets[$value->id] ?? 0;
                            $sumOfSubcategoriesTransactions += $transactions[$value->id] ?? 0;
                            $sumOfSubcategoriesAvailable += $available[$value->id] ?? 0;
                        }
                    ?>
                    <tr class="border-b bg-blue-50 dark:border-gray-800 dark:bg-green-700 dark:text-gray-200">
                        <td class="px-2 py-1 text-sm font-bold">{{ $item->name }}</td>
                        <td class="px-4 py-1 text-sm font-bold">
                            <input type="number" step=".01" value="{{number_format($sumOfSubcategoriesBudget,2,".","")}}"
                                    class="dark:bg-green-700 dark:text-gray-200 appearance-none border-none rounded w-full px-3 text-blue-800 bg-blue-50 text-right leading-tight focus:outline-none focus:shadow-outline" disabled>
                        </td>
                        <td class="px-2 py-1 text-sm font-bold">
                            <div class="dark:text-gray-200 text-blue-800 text-right w-full font-normal">{{number_format($sumOfSubcategoriesTransactions,2,".",",")}}</div>
                        </td>
                        <td class="px-2 py-1 text-sm font-bold">
                            <div class="dark:text-gray-200 text-blue-800 text-right w-full pr-2 font-normal">{{number_format($sumOfSubcategoriesAvailable,2,".",",")}}</div>
                        </td>
                    </tr>
                        @foreach($item->subcategories as $subcategory)
                        <tr class="border-b dark:border-gray-800 dark:text-gray-200">
                            <td class="px-8 py-1 text-sm"> {{ $subcategory->name }}</td>
                            <td class="px-4 py-1 text-sm">
                                <input type="number" step=".01" wire:model.lazy="budgets.{{$subcategory->id}}"
                                    class="dark:bg-gray-400 {{($budgets[$subcategory->id] ?? 0) == 0 ? 'text-gray-200' : 'text-gray-700'}} focus:text-blue-700 dark:focus:text-green-700 appearance-none border-none rounded w-full px-3 text-right leading-tight focus:outline-none focus:shadow-outline">
                            </td>
                            <td class="px-2 py-1 text-sm" style="text-align: right;">
                                {{number_format($transactions[$subcategory->id] ?? 0,2)}}
                            </td>
                            <td class="px-2 py-1 text-sm" style="text-align: right;">
                                <div class="inline-block px-2 py-1 rounded-full text-sm  {{(round($available[$subcategory->id] ?? 0,2) ?? 0) > 0 ? 'bg-green-500 text-white' : ((round($available[$subcategory->id] ?? 0,2) ?? 0) < 0 ? 'bg-red-500 text-white' : 'bg-gray-300 text-gray-700')}}">
                                    {{number_format($available[$subcategory->id] ?? 0,2)}}
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
