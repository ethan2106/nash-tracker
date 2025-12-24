<?php

// Usage : include 'button.php'; echo button('S\'inscrire', 'submit', 'primary');
function button($label, $type = 'button', $variant = 'primary')
{
    $styles = [
        'primary' => 'bg-gradient-to-r from-blue-400 to-green-400 text-white font-bold py-2 px-6 rounded-xl shadow-xl hover:scale-105 transition-transform',
        'secondary' => 'bg-white/70 text-blue-700 font-semibold py-2 px-6 rounded-xl shadow-md hover:bg-blue-100 transition',
        'danger' => 'bg-red-500 text-white font-bold py-2 px-6 rounded-xl shadow hover:bg-red-600 transition',
    ];
    $style = $styles[$variant] ?? $styles['primary'];

    return '<button type="' . $type . '" class="' . $style . '">' . $label . '</button>';
}
