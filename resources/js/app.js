import flatpickr from "flatpickr";
import "flatpickr/dist/themes/dark.css"; // Tema oscuro
import { Spanish } from "flatpickr/dist/l10n/es.js";

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
