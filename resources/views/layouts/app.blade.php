<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>