<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'language'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /*
    * Get all categories from given user
    */
    public function categories()
    {
        return $this->hasMany(Category::class, 'user_id', 'id')->orderBy('order');
    }

    /*
     * Get all accounts from given user
    */
    public function accounts()
    {
        return $this->hasMany(Account::class, 'user_id', 'id')->orderBy('name');
    }

    public function budgets($date = null, $cumulative = false)
    {
        if ($date == null)
            return $this->hasMany(Budget::class);
        else
            return $this->hasMany(Budget::class)
                ->where('date',($cumulative ? '<=' : '='), $date)->get();
    }

    public function transactionsJournals($filter = null) {
        $transactions = $this->hasMany(TransactionsJournal::class);
        if ($filter != null){
            foreach ($filter as $value) {
                $transactions->where($value['filterField'],$value['filterAs'],$value['filterTo']);
            }
        }
        return $transactions->get();
    }
}
