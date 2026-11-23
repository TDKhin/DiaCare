@props(['value'])

<label {{ $attributes->merge(['class' => 'dc-form-label']) }}>
    {{ $value ?? $slot }}
</label>
