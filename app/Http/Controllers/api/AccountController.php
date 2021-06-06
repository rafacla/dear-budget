<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        return Account::where('user_id',Auth::user()->id)->get();
    }

    public function store(Request $request)
    {
        return Account::create($request->all());
    }

    public function show($id)
    {
        $account = Account::findOrFail($id);
        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            return $account;
    }

    public function update(Request $request, $id)
    {
        $account = Account::findOrFail($id);
        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            return $account->update($request->all());
    }

    public function destroy($id)
    {
        $account = Account::findOrFail($id);
        if ($account->user_id != Auth::user()->id)
            abort(403, 'Unauthorized');
        else
            $account->delete();
    }
}
