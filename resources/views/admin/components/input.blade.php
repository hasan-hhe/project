<div class="form-group @error($name) has-error @enderror">
    <div
        class="input-group-animation @if (isset($withButtonInput)) input-group @endif {{ isset($class) ? $class : '' }}">
        <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}"
            @if (isset($id)) id="{{ $id }}" @endif class="input-new"
            @if (isset($attribute)) {{ $attribute }} @endif
            @if (isset($required) && $required) required @endif>

        @if (isset($label))
            <label class="input-label">{{ $label }}</label>
        @endif

        @if (isset($withButtonInput))
            <div class="input-group-append">
                <button class="{{ $buttonClass }}" {{ $buttonAttribute }}>
                    {{ $buttonText }}
                </button>
            </div>
        @endif
    </div>
    @error($name)
        <small class="form-text text-muted">{{ $message }}</small>
    @enderror
</div>
