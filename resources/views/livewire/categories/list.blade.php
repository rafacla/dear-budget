<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        Manage Categories
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
            <button wire:click="create()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded my-3">Create New Category</button>
            @if($isOpen)
                @include('livewire.categories.createCategory')
            @endif
            @if($isOpenSubcategory)
                @include('livewire.categories.createSubcategory')
            @endif
            <table class="table-fixed w-full">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-2 py-1">Name</th>
                        <th class="px-4 py-1">Description</th>
                        <th class="px-2 py-1">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td class="border px-2 py-1">{{ $category->name }}</td>
                        <td class="border px-4 py-1">{{ $category->description }}</td>
                        <td class="border px-2 py-1" style="text-align: right;">
                            <button wire:click="addSubcategory({{ $category->id }})" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">Add Sub</button>
                            <button wire:click="edit({{ $category->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</button>
                            <button wire:click="delete({{ $category->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                        </td>
                    </tr>
                        @foreach($category->subcategories as $subcategory)
                        <tr>
                            <td class="border px-2 py-1"><i class="fas fa-level-up-alt fa-rotate-90"></i> {{ $subcategory->name }}</td>
                            <td class="border px-4 py-1">{{ $subcategory->description }}</td>
                            <td class="border px-2 py-1" style="text-align: right;">
                                <button wire:click="editSubcategory({{ $subcategory->id }})" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Edit</button>
                                <button wire:click="deleteSubcategory({{ $subcategory->id }})" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>