<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{__('Manage Budgets')}}
    </h2>
</x-slot>
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th class="border px-2 py-1 text-sm">{{__('Name')}}</th>
                        <th class="border px-4 py-1 text-sm">{{__('Budgeted')}}</th>
                        <th class="border px-2 py-1 text-sm"></th>
                    </tr>
                </thead>
                <tbody>
                    
                    @foreach($items as $item)
                    <tr>
                        <td class="border px-2 py-1 text-sm font-bold">{{ $item->name }}</td>
                        <td class="border px-4 py-1 text-sm font-bold"></td>
                        <td class="border px-2 py-1 text-sm font-bold" style="text-align: right;">
                            
                        </td>
                    </tr>
                        @foreach($item->subcategories as $subcategory)
                        <tr>
                            <td class="border px-8 py-1 text-sm"> {{ $subcategory->name }}</td>
                            <td class="border px-4 py-1 text-sm">
                                <input type="number" step=".01" wire:model.lazy="budgets.{{$subcategory->id}}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                            </td>
                            <td class="border px-2 py-1 text-sm" style="text-align: right;">
                                
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>