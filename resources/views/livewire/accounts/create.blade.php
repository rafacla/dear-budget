<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        
      <div class="fixed inset-0 transition-opacity">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>
    
      <!-- This element is to trick the browser into centering the modal contents. -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​
    
      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
        <form>
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4;" style="display: flex;">
          <div class="" style="width: 48%;">
            <div class="mb-4">
                <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Name')}}:</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Name')}}" wire:model="form.name">
                @error('form.name') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Description')}}:</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Description')}}" wire:model="form.description">
                @error('form.description') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
            <div class="mb-4">
              <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Account Role')}}:</label>
              <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Account Role')}}" wire:model="form.role">
                @foreach ($accountRoles as $key => $value)
                    <option value="{{$key}}">{{__($value['name'])}}</option>
                @endforeach
              </select>
              @error('form.role') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
            <div class="mb-4">
              <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Account Currency')}}:</label>
              <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Currency')}}" wire:model="form.currency_id">
                @foreach ($currencies as $item)
                    <option value="{{$item->id}}">{{$item->name}}</option>
                @endforeach
              </select>
              @error('form.currency_id') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
            <div class="mb-4">
              <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Account Number')}}:</label>
              <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Account Number')}}" wire:model="form.number">
              @error('form.number') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
          </div>
          <div style="width: 25px">
          </div>
          <div class="" style="width: 48%;">
            <div class="mb-4">
              <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Bank')}}:</label>
              <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Bank')}}" wire:model="form.bank_id">
                  <option value="" selected>{{__('None')}}</option>
                @foreach ($banks as $item)
                    <option value="{{$item->id}}">{{$item->short_name}}</option>
                @endforeach
              </select>          
              @error('form.bank_id') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror 
            </div>
            <div class="mb-4">
              <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Opening Balance')}}:</label>
              <input type="number" step="0.01" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Opening Balance')}}" wire:model="form.openingbalance">
              @error('form.openingbalance') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
            <div class="mb-4">
              <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Opening Balance Date')}}:</label>
              <input type="date" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Opening Balance Date')}}" wire:model="form.openingbalancedate">
              @error('form.openingbalancedate') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
            @if ($form['role'] == 'creditCard')
            <div>
              <div class="mb-4">
                <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Statement Closing Day')}}:</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Statement Closing Day')}}" wire:model="form.statementClosingDay">
                @error('form.statementClosingDay') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
            <div class="mb-4">
                <label for="exampleFormControlInput1" class="block text-gray-700 text-sm font-bold mb-2">{{__('Statement Due Day')}}:</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="exampleFormControlInput1" placeholder="{{__('Statement Due Day')}}" wire:model="form.statementDueDay">
                @error('form.statementDueDay') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
            </div>
            </div>
            @endif
          </div>
        </div>
    
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <span class="flex w-full rounded-md shadow-sm sm:ml-3 sm:w-auto">
            <button wire:click.prevent="store()" type="button" class="inline-flex justify-center w-full rounded-md border border-transparent px-4 py-2 bg-green-600 text-base leading-6 font-medium text-white shadow-sm hover:bg-green-500 focus:outline-none focus:border-green-700 focus:shadow-outline-green transition ease-in-out duration-150 sm:text-sm sm:leading-5">
              {{__('Save')}}
            </button>
          </span>
          <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
              
            <button wire:click="closeModal()" type="reset" class="inline-flex justify-center w-full rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
              {{__('Cancel')}}
            </button>
          </span>
          </form>
        </div>
          
      </div>
    </div>
  </div>