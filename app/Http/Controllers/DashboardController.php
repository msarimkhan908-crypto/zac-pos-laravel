<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        return match (auth()->user()->role) {
            'kitchen'  => redirect()->route('orders.index'),
            'cashier'  => redirect()->route('pos.index'),
            default    => view('dashboard'),
        };
    }
}
