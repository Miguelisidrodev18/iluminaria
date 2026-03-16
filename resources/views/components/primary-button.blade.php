@props(['type' => 'submit'])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150']) }}
        style="background-color: #F7D600; color: #2B2E2C;"
        onmouseover="this.style.backgroundColor='#e8c900'"
        onmouseout="this.style.backgroundColor='#F7D600'"
        onfocus="this.style.boxShadow='0 0 0 3px rgba(247,214,0,0.4)'"
        onblur="this.style.boxShadow=''">
    {{ $slot }}
</button>
