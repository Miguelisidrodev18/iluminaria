@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 focus:border-[#F7D600] focus:ring-[#F7D600] rounded-md shadow-sm']) !!}>