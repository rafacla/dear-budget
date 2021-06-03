<div class="relative">
    <input
        type="text"
        placeholder={{__('Category')}}
        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        autocomplete="off"
        wire:model="query"
        wire:keydown.escape="resetTo"
        wire:keydown.tab="selectItem"
        wire:keydown.enter.prevent="selectItem"
        wire:keyup.arrow-up.prevent="decrementHighlight"
        wire:keydown.arrow-down.prevent="incrementHighlight"
    />
    @if (!empty($query) && $openSuggestions)
        <div class="absolute z-50 list-group bg-white w-full rounded-t-none shadow-lg p-1 max-h-44 overflow-y-scroll text-sm">
        @if (!empty($subcategories))
            @php
                $lastCategoryName = '';
            @endphp
            @foreach ($subcategories as $i => $subcategory)
            @if ($lastCategoryName != $subcategory['category']['name'])
                <div class="font-bold">{{$subcategory['category']['name']}}</div>
            @endif
            <div
                class="pl-2 hover:bg-gray-100 cursor-pointer	 {{$highlightIndex === $i ? 'bg-gray-100' : ''}}"
                wire:click="selectItem({{$i}})"
            >
                {{$subcategory['name']}}
            </div>
            @php
                $lastCategoryName = $subcategory['category']['name'];
            @endphp
            @endforeach
        @else
            <div>{{__('No category found.')}}</div>
        @endif
        </div>
    @endif
</div>
