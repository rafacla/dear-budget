<div class="relative">
    <input 
        type="text" 
        placeholder="Account Name" 
        class="typeahead-credit shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
        autocomplete="off"
        wire:model="query"
        wire:keydown.escape="resetTo"
        wire:keydown.tab="selectItem"
        wire:keydown.enter.prevent="selectItem"
        wire:keyup.arrow-up.prevent="decrementHighlight"
        wire:keydown.arrow-down.prevent="incrementHighlight"
    />
    @if (!empty($query) && $openSuggestions)
        <div class="absolute z-10 list-group bg-white w-full rounded-t-none shadow-lg p-1">
        @if (!empty($accounts))
            @foreach ($accounts as $i => $account)
                <div 
                    class="b-1 hover:bg-gray-100 {{$highlightIndex === $i ? 'bg-gray-100' : ''}}"
                    wire:click="selectItem({{$i}})"
                >
                    {{$account['name']}}
                </div>
            @endforeach
        @else
            <div class="b-1">{{__('No account found. Creating a new one.')}}</div>
        @endif
        </div>
    @endif
</div>
