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
        Subcategory::create(['name' => 'Água / Luz / Eletricidade / Gás', 'description' => 'Contas de Consumo de Concessionárias','order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Aluguel / Financiamento / Condomínio', 'description' => 'Gastos com moradia, condomínio, financiamento.', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Mensalidades', 'description' => 'Gastos com mensalidade como Spotify, YouTube, Netflix, etc..', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Internet / TV / Telefone', 'description' => 'Gastos com assinatura de Internet, TV, Celular', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        $category = Category::create([
            'name' => 'Despesas Variáveis',
            'description' => 'Categoria para suas despesas que variam como Mercado, Jantar',
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => 'Mercado', 'description' => 'Gastos com compras em mercados e padarias', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Delivery / Alimentação', 'description' => 'Gastos com Delivery, Restaurantes, Lanchonetes', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Transporte / Taxis', 'description' => 'Gastos com Taxi, Uber, Gasolina, Seguro, Locação de Carro, IPVA', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Compras Eventuais', 'description' => 'Gastos com compras que não se encaixam em uma categoria', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Saúde / Farmácia', 'description' => 'Gastos com consultas médicas, exames, remédios', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Vestiário', 'description' => 'Gastos com Roupas, Calçados', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Casa: Reformas e Manutenções', 'description' => 'Gastos reformando e fazendo manutenção da casa', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Presentes / Doações', 'description' => 'Gastos com presentes, doações e caridade', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        $category = Category::create([
            'name' => 'Longo Prazo e Economias',
            'description' => 'Categoria para programar os gastos a longo prazo ou capital para emergência',
            'order' => $category_order++,
            'user_id' => $user->id
        ]);
        $subcategory_order = 0;
        Subcategory::create(['name' => 'Férias', 'description' => 'Para aqueles valores parados em sua conta para futuras férias', 'order' => $subcategory_order++, 'category_id' => $category->id]);
        Subcategory::create(['name' => 'Seguros e Capital de Giro', 'description' => 'Para aqueles valores que você ainda não transferiu para uma conta de investimento', 'order' => $subcategory_order++, 'category_id' => $category->id]);

        return $user;
    }
}
