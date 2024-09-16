<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use DB;
use App\Http\Controllers\Controller;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}

// class UserController extends Controller
// {
//     /**
//      * Show a list of all of the application's users.
//      *
//      * @return Response
//      */
//     public function index()
//     {
//         $users = DB::select('select * from users where active = ?', [1]);
 
//         return view('user.index', ['users' => $users]);
//     }
// }
