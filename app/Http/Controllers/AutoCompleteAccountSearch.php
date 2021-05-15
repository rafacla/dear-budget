<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AutoCompleteAccountSearch extends Controller
{
    public function search(Request $request)
    {
          $search = $request->get('term');
      
          $result = Account::where('name', 'LIKE', '%'. $search. '%')->get();
 
          return response()->json($result);
            
    } 
}
