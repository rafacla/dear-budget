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
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['required', 'accepted'] : '',
        ])->validate();
        
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);

        $category_order = 0;

        $category = Category::create([
            'name' => 'Despesas Mensais',
            'description' => 'Categoria para suas despesas fixas',
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => 'Água', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Luz / Eletricidade', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Aluguel / Financiamento / Condomínio', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Mensalidades', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Internet / TV', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Telefone', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        $category = Category::create([
            'name' => 'Despesas Variáveis',
            'description' => 'Categoria para suas despesas que variam como Mercado, Jantar',
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => 'Mercado', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Delivery / Alimentação', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Transporte / Taxis', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Compras Eventuais', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Saúde / Farmácia', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Vestiário', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Casa: Reformas e Manutenções', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Presentes / Doações', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        $category = Category::create([
            'name' => 'Longo Prazo e Economias',
            'description' => 'Categoria para programar os gastos a longo prazo ou capital para emergência',
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => 'Férias', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Seguros e Capital de Giro', 'order' => $subcategory_order++, 'category_id' => $category->id]);

        return $user;
    }
}
