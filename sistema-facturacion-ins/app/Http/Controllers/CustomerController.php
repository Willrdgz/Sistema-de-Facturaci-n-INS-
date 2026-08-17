<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function index(): View
    {
        $customers = Customer::query()->when(request('search'), fn ($query, $search) => $query->where('name', 'like', "%{$search}%")->orWhere('document', 'like', "%{$search}%"))->latest()->paginate(10)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated() + ['active' => $request->boolean('active')]);

        return to_route('customers.index')->with('success', 'Cliente registrado correctamente.');
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated() + ['active' => $request->boolean('active')]);

        return to_route('customers.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->update(['active' => false]);

        return back()->with('success', 'Cliente desactivado.');
    }
}
