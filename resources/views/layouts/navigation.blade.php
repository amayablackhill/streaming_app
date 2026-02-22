@php
    // Backward-compatible proxy: all navigation now goes through the Cineclub top-nav component.
    $navContext = request()->is('admin') || request()->is('admin/*') ? 'admin' : 'editorial';
@endphp

<x-ui.top-nav :context="$navContext" />
