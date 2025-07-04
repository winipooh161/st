<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Получаем только полученные/сохраненные шаблоны текущего пользователя (с суффиксом "(копия)" в названии)
        $userTemplates = \App\Models\UserTemplate::where('user_id', Auth::id())
            ->where('name', 'like', '% (копия)')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('home', compact('userTemplates'));
    }
}
