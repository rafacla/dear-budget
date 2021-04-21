<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Categories extends Component
{
    public $categories, $category_name, $category_description, $category_id, $category_order;
    public $subcategories, $subcategory_name, $subcategory_description, $subcategory_id, $subcategory_order;
    public $isOpen = 0;

    public function render()
    {
        $this->categories = Category::all();
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
    }
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function openModal()
    {
        $this->isOpen = true;
    }
  
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function closeModal()
    {
        $this->isOpen = false;
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
    }
     
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public function store()
    {
        $this->validate([
            'category_name' => 'required',
            'category_description' => 'required',
        ]);
   
        Category::updateOrCreate(['id' => $this->category_id], [
            'category_name' => $this->category_name,
            'category_description' => $this->category_description
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
        $this->category_id = $id;
        $this->category_name = $category->name;
        $this->category_description = $category->description;
    
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
        session()->flash('message', 'category Deleted Successfully.');
    }
}
