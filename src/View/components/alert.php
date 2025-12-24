<?php

// Usage : include 'alert.php'; echo alert('success', 'Inscription réussie !');
if (!function_exists('alert'))
{
    function alert($type = 'info', $message = '')
    {
        $colors = [
            'success' => 'bg-green-100 border-green-400 text-green-800',
            'error'   => 'bg-red-100 border-red-400 text-red-800',
            'info'    => 'bg-blue-100 border-blue-400 text-blue-800',
            'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-800',
        ];
        $icons = [
            'success' => '<i class="fa-solid fa-circle-check text-green-400 mr-2" aria-hidden="true"></i>',
            'error'   => '<i class="fa-solid fa-circle-xmark text-red-400 mr-2" aria-hidden="true"></i>',
            'info'    => '<i class="fa-solid fa-circle-info text-blue-400 mr-2" aria-hidden="true"></i>',
            'warning' => '<i class="fa-solid fa-triangle-exclamation text-yellow-400 mr-2" aria-hidden="true"></i>',
        ];
        $color = $colors[$type] ?? $colors['info'];
        $icon = $icons[$type] ?? $icons['info'];

        return '<div class="flex items-center p-4 mb-4 rounded-xl border ' . $color . ' shadow transition-all animate-fade-in">
            ' . $icon . '
            <span class="font-medium">' . htmlspecialchars($message) . '</span>
        </div>';
    }
}
