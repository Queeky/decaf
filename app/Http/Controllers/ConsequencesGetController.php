<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConsequencesGetController extends Controller {
    function main(Request $request) {
        return view('consequences'); 
    }
}
