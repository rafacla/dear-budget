<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array  $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'language'  => ['required','string'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();
        
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'language' => $input['language'],
            'password' => Hash::make($input['password']),
        ]);

        $category_order = 0;
        app()->setLocale($input['language']);
        
        $category = Category::create([
            'name' => __('Monthly Expenses'),
            'expense' => true,
            'description' => __('Category for your fixed expenses'),
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => __('Utilities'), 'description' => '','order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Home Rent / Mortgage'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Membership fees'), 'description' => 'Netflix, Youtube, Sportify...', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Internet / Cable TV / Cellphone'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        $category = Category::create([
            'name' => __('Daily Expenses'),
            'description' => __('Category for your not fixed expenses like groceries, dinner, doctor appointments'),
            'expense' => true,
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => __('Groceries'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Dinner, Meals and Food delivery'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Taxis and Car related expenses'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Random purchases'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Health and medicine'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Clothing'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Home: repairs and materials'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Gifts and donations'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        $category = Category::create([
            'name' => __('Long term and savings'),
            'description' => __('Category for saving for future expenses and emergency fund'),
            'expense' => 1,
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => __('Vacation saving'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Saving for later expenses'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        $category = Category::create([
            'name' => __('Incomes'),
            'description' => __('Category for income transactions'),
            'expense' => 0,
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => __('Salary'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Earnings from investments'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => __('Other sources'), 'description' => '', 'order' => $subcategory_order++, 'category_id' => $category->id]);

        return $user;
    }
}
