@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'dc-form-control']) }}>
