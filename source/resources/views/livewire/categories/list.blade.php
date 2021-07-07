<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight dark:text-gray-200">
        {{__('Manage Categories')}}
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
                    <button wire:click="create()" class="dark:bg-green-500 dark:hover:bg-green-700 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">{{__('Create New Category')}}</button>
                </div>
                <div class="self-center mx-2">
                    <select class="dark:bg-gray-300 shadow appearance-none  rounded w-full py-2 px-12 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1"  wire:model="categoryFilter">
                        <option value="expenses">{{__('Expense categories')}}</option>
                        <option value="incomes">{{__('Income categories')}}</option>
                    </select>
                </div>
            </div>

            @if($isOpen)
                @include('livewire.categories.createCategory')
            @endif
            @if($isOpenSubcategory)
                @include('livewire.categories.createSubcategory')
            @endif
            <table class="table-auto w-full">
                <thead>
                    <tr class="bg-gray-100 dark:bg-gray-700 dark:text-gray-200 rounded-lg">
                        <th class="px-2 py-1 text-sm">{{__('Name')}}</th>
                        <th class="px-4 py-1 text-sm">{{__('Description')}}</th>
                        <th class="px-2 py-1 text-sm"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr class="border-b dark:border-gray-800 dark:text-gray-200">
                        <td class="px-2 py-1 text-sm font-bold">{{ $category->name }}</td>
                        <td class="px-4 py-1 text-sm font-bold">{{ $category->description }}</td>
                        <td class="px-2 py-1 text-sm font-bold" style="text-align: right;">
                            <button wire:click="addSubcategory({{ $category->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-1 px-2 rounded"><i class="far fa-plus-square"></i></button>
                            <button wire:click="edit({{ $category->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded"><i class="far fa-edit"></i></button>
                            <button wire:click="delete({{ $category->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"><i class="far fa-trash-alt"></i></button>
                            <button wire:click="moveCategoryUp({{ $category->id }})" class="bg-gray-200 hover:bg-gray-400 text-gray-600 font-bold py-1 px-1 rounded"><i class="fas fa-arrow-up"></i></button>
                            <button wire:click="moveCategoryDown({{ $category->id }})" class="bg-gray-200 hover:bg-gray-400 text-gray-600 font-bold py-1 px-1 rounded"><i class="fas fa-arrow-down"></i></button>
                        </td>
                    </tr>
                        @foreach($category->subcategories as $subcategory)
                        <tr class="dark:text-gray-200">
                            <td class="px-8 py-1 text-sm"> {{ $subcategory->name }}</td>
                            <td class="px-4 py-1 text-sm">{{ $subcategory->description }}</td>
                            <td class="px-2 py-1 text-sm" style="text-align: right;">
                                <button wire:click="editSubcategory({{ $subcategory->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-1 px-2 rounded"><i class="far fa-edit"></i></button>
                                <button wire:click="deleteSubcategory({{ $subcategory->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-1 px-2 rounded"><i class="far fa-trash-alt"></i></button>
                                <button wire:click="moveSubcategoryUp({{ $subcategory->id }})" class="bg-gray-200 hover:bg-gray-400 text-gray-600 font-bold py-1 px-1 rounded"><i class="fas fa-arrow-up"></i></button>
                                <button wire:click="moveSubcategoryDown({{ $subcategory->id }})" class="bg-gray-200 hover:bg-gray-400 text-gray-600 font-bold py-1 px-1 rounded"><i class="fas fa-arrow-down"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
