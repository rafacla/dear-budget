<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{__('Manage Accounts')}}
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
            <button wire:click="create()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">{{__('Create New Account')}}</button>
            @if($isOpen)
                @include('livewire.accounts.create')
            @endif
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 w-20">Id.</th>
                        <th class="px-4 py-2">{{__('Name')}}</th>
                        <th class="px-4 py-2">{{__('Account Role')}}</th>
                        <th class="px-2 py-2">{{__('Account Number')}}</th>
                        <th class="px-4 py-2">{{__('Current Balance')}}</th>
                        <th class="px-4 py-2">{{__('Last Transaction Date')}}</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                    <tr>
                        <td class="border px-4 py-2">{{ $item->id }}</td>
                        <td class="border px-4 py-2">{{ $item->name }}</td>
                        <td class="border px-4 py-2">{{ __($accountRoles[$item->role]) }}</td>
                        <td class="border px-2 py-2">{{ $item->number }}</td>
                        <td class="border px-4 py-2"></td>
                        <td class="border px-4 py-2"></td>
                        <td class="border px-4 py-2">
                        <button wire:click="edit({{ $item->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded">{{__('Edit')}}</button>
                            <button wire:click="delete({{ $item->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded">{{__('Delete')}}</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>