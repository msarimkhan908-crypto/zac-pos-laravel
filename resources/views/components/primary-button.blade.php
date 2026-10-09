<button {{ $attributes->merge([
    'type' => 'submit',
    'class' => 'btn-primary inline-flex items-center font-semibold text-xs uppercase tracking-widest focus:outline-none transition ease-in-out duration-150',
]) }}>
    {{ $slot }}
</button>
