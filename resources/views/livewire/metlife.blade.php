<div>
    <div
        class="relative h-full flex-1 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
        <div
            class="relative flex-1 overflow-auto border border-neutral-200 dark:border-neutral-700 p-4">
            <div class="">
                <div class="">
                    <div class="relative" wire:ignore>
                        <input
                            type="text"
                            id="date"
                            name="date"
                            placeholder="Selecciona una fecha"
                            class="block rounded-lg border border-gray-300 px-4 py-2 pr-20 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-full"
                        >
                        <!-- icono calendario -->
                        <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                                 viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Input hidden que se sincroniza con Livewire -->
                <input type="hidden" id="MetlifeDate" wire:model.live="MetlifeDate">
            </div>
        </div>

        @if($MetlifeDate && $orders)
            <div
                class="relative flex-1 overflow-auto border border-neutral-200 dark:border-neutral-700 p-4 mt-3 mb-3">
                <form wire:submit.prevent="updatedCsvFile">
                    <div class="flex items-center gap-3">
                        <label class="text-base font-bold text-shadow-white">Transacciones Core SuperApp</label>
                        <input type="file"
                               class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                               wire:model="csvFile"
                               accept="text/csv"
                        />
                        @error('csvFile') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </form>
            </div>

            @if ($csvData)
                <div
                    class="relative flex-1 overflow-auto border border-neutral-200 dark:border-neutral-700 p-4 mt-3 mb-3">
                    <form wire:submit.prevent="updatedPoliceFile">
                        <div class="flex items-center gap-3">
                            <label class="text-base font-bold text-shadow-white">Transacciones Metlife</label>
                            <input type="file"
                                   class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                                   wire:model="policeFile"
                                   accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            />
                            @error('policeFile') <span
                                class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </form>
                </div>

            @endif
        @endif

        <div class="w-full mt-2">
            <table class="border-collapse border border-neutral-200 dark:border-neutral-700 w-full">
                <thead>
                <tr>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Código</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Cédula</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Nombre Cliente</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Método de pago</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Teléfono Cliente</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Valor pagado</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Estado Orden</th>
                    {{--                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Metlife</th>--}}
                </tr>
                </thead>
                <tbody>
                @if ($csvData)
                    @foreach ($csvData as $row)
                        <tr class="text-center">
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Sales Order Code'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Customer Document'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Customer Full Name'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Payment Method'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Customer Phone Number'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Payment Amount'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700 text-center">
                                <span
                                    class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">{{ $row['Sales Order Status'] }}</span>
                            </td>
                            {{--                                <td class="border p-4 border-neutral-200 dark:border-neutral-700">--}}
                            {{--                                    <button wire:click="openModal({{ json_encode($row) }})" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-xs p-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">--}}
                            {{--                                        Validar--}}
                            {{--                                    </button>--}}
                            {{--                                </td>--}}
                        </tr>
                    @endforeach
                @else
                    <tr class="justify-center">
                        <td colspan="7" class="border p-4 border-neutral-200 dark:border-neutral-700 text-center">Sin
                            información para mostrar
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>



