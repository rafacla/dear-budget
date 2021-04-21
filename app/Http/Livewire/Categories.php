<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class Categories extends Component
{
    public $categories, $category_name, $category_description, $category_id, $category_order;
    public $subcategory_name, $subcategory_description, $subcategory_id, $subcategory_order, $subcategory_category_id;
    public $isOpen = 0;
    public $isOpenSubcategory = 0;
    

    public function render()
    {
        $this->categories = Auth::user()->categories;
        return view('livewire.categories.list');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
        $this->category_order = $this->categories->max('order')+1;
    }
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function openModal()
    {
        $this->isOpen = true;
        $this->isOpenSubcategory = false;
    }
    public function openSubcategoryModal()
    {
        $this->isOpen = false;
        $this->isOpenSubcategory = true;
    }
    
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function closeModal()
    {
        $this->isOpen = false;
        $this->isOpenSubcategory = false;
    }
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    private function resetInputFields(){
        $this->category_name = '';
        $this->category_description = '';
        $this->category_id = '';
        $this->category_order = '';
        $this->subcategory_name = '';
        $this->subcategory_description = '';
        $this->subcategory_id = '';
        $this->subcategory_category_id = '';
        $this->subcategory_order = '';
    }
     
    public function storeSub()
    {
        $this->validate([
            'subcategory_name' => 'required',
            'subcategory_description' => 'required',
            'subcategory_category_id' => 'required',
        ]);    
        
        Subcategory::updateOrCreate(['id' => $this->subcategory_id], [
            'name' => $this->subcategory_name,
            'description' => $this->subcategory_description,
            'order' => $this->subcategory_order,
            'category_id' => $this->subcategory_category_id
        ]);
  
        session()->flash('message', 
            $this->subcategory_id ? __('Subcategory updated Successfully.') : __('Subcategory created Successfully.'));
  
        $this->closeModal();
        $this->resetInputFields();
    }
    public function store()
    {
        $this->validate([
            'category_name' => 'required',
            'category_description' => 'required',
        ]);        
   
        Category::updateOrCreate(['id' => $this->category_id], [
            'name' => $this->category_name,
            'description' => $this->category_description,
            'order' => $this->category_order,
            'user_id' => Auth::user()->id
        ]);
  
        session()->flash('message', 
            $this->category_id ? 'category Updated Successfully.' : 'category Created Successfully.');
  
        $this->closeModal();
        $this->resetInputFields();
    }
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        if ($category->user_id != Auth::id()) {
            abort(403, __('You don\'t have privileges for this') );
        }
        $this->category_id = $id;
        $this->category_name = $category->name;
        $this->category_description = $category->description;
        $this->category_order = $category->order;
    
        $this->openModal();
    }
     
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function delete($id)
    {
        category::find($id)->delete();
        session()->flash('message', __('Category deleted successfully.'));
    }

    public function addSubcategory($category_id) {
        $this->resetInputFields();
        $this->openSubcategoryModal();
        $category = Category::findOrFail($category_id);
        $this->subcategory_order = $category->subcategories->max('order')+1;
        $this->subcategory_category_id = $category_id;
    }

    public function editSubcategory($id) {
        $subcategory = Subcategory::findOrFail($id);
        $category = $subcategory->category;
        if ($category->user_id != Auth::id()) {
            abort(403, __('You don\'t have privileges for this') );
        }
        $this->subcategory_id = $id;
        $this->subcategory_name = $subcategory->name;
        $this->subcategory_description = $subcategory->description;
        $this->subcategory_order = $subcategory->order;
        $this->subcategory_category_id = $category->id;
        $this->openSubcategoryModal();
    }

    public function deleteSubcategory($id) {
        subcategory::find($id)->delete();
        session()->flash('message', __('Subcategory deleted successfully.'));
    }
}
