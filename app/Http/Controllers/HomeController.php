<?php

namespace App\Http\Controllers;

use App\Models\CashSession;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
        // Redirect to React SPA admin panel
        // In production, change this to your deployed React SPA URL
        $spaUrl = config('app.spa_url', 'http://localhost:3000');

        return redirect($spaUrl);
    }
}
