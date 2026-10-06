@extends('layouts.app')
@section('heading','Nuevo producto')
@section('content')
    <form class="card max-w-3xl" method="POST" action="{{ route('products.store') }}">
        @csrf
        @include('products.form')
        <div class="form-actions">
            <a class="btn-secondary" href="{{ route('products.index') }}">
                Cancelar
            </a>
            <button class="btn-primary">
                Guardar producto
            </button>
        </div>
    </form>
@endsection
