@extends('layouts.app')
@section('heading',$account->exists?'Editar usuario':'Nuevo usuario')
@section('content')
    <form class="card max-w-3xl space-y-4" method="POST" action="{{ $account->exists?route('users.update',$account):route('users.store') }}">
        @csrf
        @if($account->exists)
            @method('PUT')
        @endif
        <label class="block">
            Nombre
            <input class="input" name="name" required value="{{ old('name',$account->name) }}">
        </label>
        <label class="block">
            Correo
            <input class="input" name="email" type="email" required value="{{ old('email',$account->email) }}">
        </label>
        <label class="block">
            Perfil
            <select name="role" class="input">
                <option value="seller" @selected(old('role',$account->
                    role)==='seller')>Vendedor
                </option>
                <option value="admin" @selected(old('role',$account->
                    role)==='admin')>Administrador
                </option>
            </select>
        </label>
        <label class="block">
            Estado
            <select class="input" name="active">
                <option value="1" @selected(old('active',$account->
                    active)==1)>Activo
                </option>
                <option value="0" @selected(old('active',$account->
                    active)==0)>Inactivo
                </option>
            </select>
        </label>
        <label class="block">
            Contraseña
            {{ $account->exists?'(dejar vacía para conservar)':'' }}
            <input class="input" name="password" type="password" minlength="10" autocomplete="new-password" @required(!$account->
            exists)>
        </label>
        <label class="block">
            Confirmar contraseña
            <input class="input" name="password_confirmation" type="password" autocomplete="new-password">
        </label>
        <div class="form-actions">
            <a class="btn-secondary" href="{{ route('users.index') }}">
                Cancelar
            </a>
            <button class="btn-primary">
                Guardar
            </button>
        </div>
    </form>
@endsection
