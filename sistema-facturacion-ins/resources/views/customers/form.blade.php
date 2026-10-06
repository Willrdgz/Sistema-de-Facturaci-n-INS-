<div class="grid gap-5 md:grid-cols-2">
    <label>
        Nombre completo *
        <input class="input" name="name" required value="{{ old('name',$customer->name ?? '') }}">
    </label>
    <label>
        Documento
        <input class="input" name="document" value="{{ old('document',$customer->document ?? '') }}">
    </label>
    <label>
        Teléfono
        <input class="input" name="phone" value="{{ old('phone',$customer->phone ?? '') }}">
    </label>
    <label>
        Correo
        <input class="input" type="email" name="email" value="{{ old('email',$customer->email ?? '') }}">
    </label>
    <label class="md:col-span-2">
        Dirección
        <textarea class="input" name="address">{{ old('address',$customer->address ?? '') }}</textarea>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="active" value="1" @checked(old('active',$customer->
        active ?? true))> Cliente activo
    </label>
</div>
