<div class="fixed z-10 inset-0 overflow-y-auto ease-out duration-400">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

      <div class="fixed inset-0 transition-opacity">
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
      </div>

      <!-- This element is to trick the browser into centering the modal contents. -->
      <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>​

      <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all 
        sm:my-8 sm:align-middle sm:w-auto" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <p class="w-full font-bold w-auto">{{__('Credit Cards from Account')}}</p>
          <table class="table-auto w-full">
            <thead>
              <tr>
                <th>{{__('Name')}}</th>
                <th>{{__('Number')}}</th>
                <th>{{__('Description')}}</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              @foreach (($creditCardsFromAccount ?? []) as $item)
                  <tr>
                    <td>{{$item->name}}</td>
                    <td>{{$item->number}}</td>
                    <td>{{$item->description}}</td>
                    <td>
                      <button wire:click="editCC({{$item->id}})"><i class="far fa-edit"></i></button>
                      <button wire:click="deleteCC({{$item->id}})"><i class="far fa-minus-square"></i></button>
                    </td>
                  </tr>
              @endforeach
            </tbody>
          </table>
          <form>
            <div class="w-full flex">
              <div class="mb-4 mr-2">
                <input type="text" class="shadow appearance-none border rounded w-auto py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="{{__('Credit Card Name')}}" wire:model.lazy="formCC.name">
                @error('formCC.name') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
              </div>
              <div class="mb-4 mx-2">
                <input type="text" class="shadow appearance-none border rounded w-auto py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="{{__('Credit Card Number')}}" wire:model.lazy="formCC.number">
                @error('formCC.number') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
              </div>
              <div class="mb-4 mx-2">
                <input type="text" class="shadow appearance-none border rounded w-auto py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="{{__('Credit Card Description')}}" wire:model.lazy="formCC.description">
                @error('formCC.description') <span class="text-red-500 text-xs">{{ __($message) }}</span>@enderror
              </div>
              <button wire:click="storeCC()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold px-2 py-0.5 h-auto rounded">
                {{__('Save')}}
              </button>
            </div>
          </form>
        </div>

        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <span class="mt-3 flex w-full rounded-md shadow-sm sm:mt-0 sm:w-auto">
            <button wire:click="closeModal()" class="inline-flex justify-center w-auto rounded-md border border-gray-300 px-4 py-2 bg-white text-base leading-6 font-medium text-gray-700 shadow-sm hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue transition ease-in-out duration-150 sm:text-sm sm:leading-5">
              {{__('Close')}}
            </button>
          </span>
        </div>

      </div>
    </div>
  </div>
