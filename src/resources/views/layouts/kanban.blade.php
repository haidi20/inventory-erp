<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Papan Kanban' }}</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/kanban.ts'])
</head>
<body class="kanban-page">
    {{ $slot }}
    @livewireScripts
</body>
</html>
