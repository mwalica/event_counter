<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:ital,opsz,wght@0,6..12,200..1000;1,6..12,200..1000&display=swap" rel="stylesheet">


        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
        @fluxAppearance
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800 antialiased">

    <flux:header container class="bg-zinc-50 dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />
        <flux:navbar class="-mb-px max-lg:hidden">
            <flux:navbar.item icon="calendar-days" href="{{route('event.index')}}" wire:navigate>Events</flux:navbar.item>
            <flux:navbar.item icon="tag" href="{{route('type.index')}}" wire:navigate>Types</flux:navbar.item>
            <flux:navbar.item icon="archive" href="{{route('archive.index')}}" wire:navigate>Archive</flux:navbar.item>
            <flux:separator vertical variant="subtle" class="my-2"/>
            <flux:navbar.item icon="log-out" href="{{route('logout')}}" wire:navigate>Logout</flux:navbar.item>
        </flux:navbar>
        <flux:spacer />
        <flux:switch x-data x-model="$flux.dark" label="Dark mode"  />
    </flux:header>

    <flux:sidebar sticky collapsible="mobile" class="lg:hidden bg-zinc-50 dark:bg-zinc-900">
        <flux:sidebar.header>
            <flux:sidebar.collapse class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-mr-2" />
        </flux:sidebar.header>
        <flux:sidebar.nav>
            <flux:sidebar.item icon="home" href="#">Events</flux:sidebar.item>
            <flux:sidebar.item icon="inbox" href="#">Types</flux:sidebar.item>
            <flux:sidebar.item icon="document-text" href="#">Archive</flux:sidebar.item>
        </flux:sidebar.nav>
        <flux:sidebar.spacer />
        <flux:sidebar.nav>
            <flux:sidebar.item icon="cog-6-tooth" href="#">Logout</flux:sidebar.item>
        </flux:sidebar.nav>

    </flux:sidebar>

    <flux:main container class="lg:mt-0">
        {{ $slot }}
    </flux:main>

        @livewireScripts
        @fluxScripts
    </body>
</html>
