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
        $colors = $request->validate([
            'theme_colors' => 'sometimes|array:sidebar,background,primary,header',
            'theme_colors.sidebar' => ['required_with:theme_colors', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_colors.background' => ['required_with:theme_colors', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_colors.primary' => ['required_with:theme_colors', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_colors.header' => ['required_with:theme_colors', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);
        $data = array_merge($data, $colors);
        $business = Business::first() ?? new Business;
        $business->fill($data)->save();

        return back()->with('success', 'Configuración del negocio y apariencia actualizadas.');
    }
}
