<div class="form-group @error($name) has-error @enderror">
    <div class="input-group-animation {{ isset($class) ? $class : '' }}">
        <textarea name="{{ $name }}"
            @if (isset($id)) id="{{ $id }}" @endif class="input-new"
            @if (isset($attribute)) {{ $attribute }} @endif @if (isset($required) && $required) required @endif>
            {{ $value }}
        </textarea>
        <label class="input-label">{{ $label }}</label>

        @error($name)
            <small class="form-text text-muted">{{ $message }}</small>
        @enderror
    </div>
</div>
