<?php

if (!function_exists('get_menu')) {
    function get_menu(string $section = 'main_menu'): array
    {
        return \App\Services\ConfigService::getMenu($section);
    }
}