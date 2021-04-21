<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{__('Manage Banks')}}
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
            <button wire:click="create()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">{{__('Create New Bank')}}</button>
            @if($isOpen)
                @include('livewire.banks.create')
            @endif
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2 w-20">Id.</th>
                        <th class="px-4 py-2">{{__('Short Name')}}</th>
                        <th class="px-4 py-2">{{__('Full Name')}}</th>
                        <th class="px-4 py-2">{{__('Country')}}</th>
                        <th class="px-4 py-2">{{__('Icon')}}</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($banks as $bank)
                    <tr>
                        <td class="border px-4 py-2">{{ $bank->id }}</td>
                        <td class="border px-4 py-2">{{ $bank->short_name }}</td>
                        <td class="border px-4 py-2">{{ $bank->full_name }}</td>
                        <td class="border px-4 py-2">{{ $bank->country }}</td>
                        <td class="border px-4 py-2">{{ $bank->icon }}</td>
                        <td class="border px-4 py-2">
                        <button wire:click="edit({{ $bank->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">{{__('Edit')}}</button>
                            <button wire:click="delete({{ $bank->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">{{__('Delete')}}</button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>