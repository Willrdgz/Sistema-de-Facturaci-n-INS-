<div class="grid gap-5">
    <label>
        Nombre *
        <input class="input" name="name" required value="{{ old('name',$category->name ?? '') }}">
    </label>
    <label>
        Descripción
        <textarea class="input" name="description">{{ old('description',$category->description ?? '') }}</textarea>
    </label>
    <label class="flex items-center gap-2">
        <input type="checkbox" name="active" value="1" @checked(old('active',$category->
        active ?? true))> Categoría activa
    </label>
</div>
