@extends('layouts.app')
@section('heading','Configuración del negocio')
@section('content')
    <form class="card max-w-3xl grid gap-5 md:grid-cols-2" method="POST" action="{{ route('business.update') }}">
        @csrf
        @method('PUT')
        @foreach(['name'=>'Nombre del negocio','trade_name'=>'Nombre comercial','tax_id'=>'Identificación fiscal','phone'=>'Teléfono','email'=>'Correo','address'=>'Dirección','tax_rate'=>'Impuesto (%)'] as $field=>$label)
            <label>
                {{ $label }}
                <input class="input" name="{{ $field }}" value="{{ old($field,$business->$field) }}" type="{{ $field==='tax_rate'?'number':($field==='email'?'email':'text') }}" @if($field==='tax_rate') min="0" max="100" step="0.01" @endif @required(in_array($field,['name','tax_rate']))>
            </label>
        @endforeach
        <section class="md:col-span-2 border-t pt-5" data-theme-settings>
            <h2 class="text-lg font-bold">Colores del sistema</h2>
            <p class="text-sm text-slate-500 mt-1 mb-4">Personaliza la apariencia para todos los usuarios. Los cambios se aplican al guardar.</p>
            <div class="grid gap-4 sm:grid-cols-2">
                @foreach(['sidebar' => 'Menú lateral', 'background' => 'Fondo de la página', 'primary' => 'Botones y opción activa', 'header' => 'Barra superior'] as $key => $label)
                    <label class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 p-3">
                        <span>{{ $label }}</span>
                        <input type="color" name="theme_colors[{{ $key }}]" value="{{ old('theme_colors.'.$key, $business->layoutColors()[$key]) }}" data-default="{{ \App\Models\Business::DEFAULT_COLORS[$key] }}" class="h-10 w-16 cursor-pointer" aria-label="{{ $label }}">
                    </label>
                @endforeach
            </div>
            <button type="button" class="btn-secondary mt-4" data-reset-theme>Restablecer colores originales</button>
        </section>
        <div class="form-actions md:col-span-2">
            <button class="btn-primary">
                Guardar configuración
            </button>
        </div>
    </form>
@endsection
