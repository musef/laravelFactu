<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Company;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // obtenemos el usuario
        $user=Auth::guard('')->user();
        
        // obtenemos el nombre de la compañia
        $companyName=Company::findOrFail($user->idcompany)->company_name;
        
        return view('home')
            ->with('companyName',$companyName);
    }
}
