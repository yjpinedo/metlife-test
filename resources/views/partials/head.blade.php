<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? config('app.name') }}</title>

<link rel="icon" href="/favicon.ico" sizes="any">
<link rel="icon" href="/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/apple-touch-icon.png">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance

{{--<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">--}}
<style>
    /* Cambiar color de fondo del calendario */
    .flatpickr-calendar {
        background-color: #111827 !important; /* gris muy oscuro estilo Tailwind (bg-gray-900) */
        border: 1px solid #374151 !important; /* borde gris oscuro */
    }

    /* Cambiar color de los días */
    .flatpickr-day {
        color: #d1d5db; /* text-gray-300 */
    }

    /* Hover en los días */
    .flatpickr-day:hover {
        background-color: #2563eb !important; /* azul Tailwind */
        color: #fff !important;
    }

    /* Día seleccionado */
    .flatpickr-day.selected {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
    }

    .flatpickr-weekday {
       line-height: 2.5 !important;
    }

    /* Encabezado (mes, año, flechas) */
    .flatpickr-months,
    .flatpickr-weekdays {
        background-color: #1f2937 !important; /* bg-gray-800 */
        color: #e5e7eb !important; /* text-gray-200 */
    }

    /* Inputs de hora si usas enableTime */
    .flatpickr-time input {
        background-color: #111827 !important;
        color: #f3f4f6 !important;
        border: 1px solid #374151 !important;
    }
</style>

