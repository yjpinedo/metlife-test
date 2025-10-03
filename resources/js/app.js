import flatpickr from "flatpickr";
import "flatpickr/dist/themes/dark.css"; // Tema oscuro
import { Spanish } from "flatpickr/dist/l10n/es.js";
import Swal from 'sweetalert2';

document.addEventListener("livewire:navigated", () => {
    // Inicializar Flatpickr
    flatpickr("#date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "d/m/Y",
        locale: Spanish,
        theme: "dark",
        onChange: function (selectedDates, dateStr) {
            document.getElementById("MetlifeDate").value = dateStr;
            document.getElementById("MetlifeDate").dispatchEvent(new Event('input', {bubbles: true}));
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    Livewire.on('clear-order-date', event => {
        Swal.fire({
            icon: event[0].icon,
            title: event[0].title || 'Error',
            text: event[0].text || '',
            confirmButtonColor: '#615fff',
            confirmButtonText: 'Listo',
            background: '#404040',
            color: '#e5e7eb',
        });
    });

    Livewire.on('clear-order-file', event => {
        Swal.fire({
            icon: event[0].icon,
            title: event[0].title || 'Error',
            text: event[0].text || '',
            confirmButtonColor: '#615fff',
            confirmButtonText: 'Listo',
            background: '#404040',
            color: '#e5e7eb',
        });
    });

    Livewire.on('incorrect-file-format', event => {
        Swal.fire({
            icon: event[0].icon,
            title: event[0].title || 'Error',
            text: event[0].text || '',
            confirmButtonColor: '#615fff',
            confirmButtonText: 'Listo',
            background: '#404040',
            color: '#e5e7eb',
        });
    });

    Livewire.on('pending-functionality', event => {
        Swal.fire({
            icon: event[0].icon,
            title: event[0].title || 'Error',
            text: event[0].text || '',
            confirmButtonColor: '#615fff',
            confirmButtonText: 'Listo',
            background: '#404040',
            color: '#e5e7eb',
        });
    });
});
