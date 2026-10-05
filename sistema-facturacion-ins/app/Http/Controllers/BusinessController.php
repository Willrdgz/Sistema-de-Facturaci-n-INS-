<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BusinessController extends Controller
{
    public function edit(): View
    {
        return view('business.edit', ['business' => Business::first() ?? new Business(['tax_rate' => 13])]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:255', 'trade_name' => 'nullable|string|max:255', 'tax_id' => 'nullable|string|max:30', 'phone' => 'nullable|string|max:25', 'email' => 'nullable|email|max:255', 'address' => 'nullable|string|max:500', 'tax_rate' => 'required|numeric|min:0|max:100']);
        $business = Business::first() ?? new Business;
        $business->fill($data)->save();

        return back()->with('success', 'Datos del negocio actualizados.');
    }
}
