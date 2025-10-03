<div>
    <div
        class="relative h-full flex-1 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">
        <div
            class="relative flex-1 overflow-auto border border-neutral-200 dark:border-neutral-700 p-4">
            <div class="grid gap-6 sm:grid-cols-4">
                <div class="">
                    <div class="relative" wire:ignore>
                        <input
                            type="text"
                            id="date"
                            name="date"
                            placeholder="Selecciona una fecha"
                            class="block rounded-lg border border-gray-300 px-4 py-2 pr-20 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                        >
                        <!-- icono calendario -->
                        <div class="pointer-events-none absolute inset-y-0 left-58 flex items-center">
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
                @if($MetlifeDate && $orders)
                    <div>
                        {{--<input class="block w-full text-sm p-4 text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400" id="file_input" type="file">--}}
                        <form wire:submit.prevent="updatedCsvFile">
                            <input type="file"
                                   class="block text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                                   wire:model="csvFile"
                                   accept="text/csv"
                            />
                            @error('csvFile') <span class="text-danger">{{ $message }}</span> @enderror
                        </form>
                    </div>
                @endif
            </div>
        </div>

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
                    {{--                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Metlife</th>--}}
                </tr>
                </thead>
                <tbody>
                @if ($csvData)
                    @foreach ($csvData as $row)
                        <tr>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Sales Order Code'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Customer Document'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Customer Full Name'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700 text-center">{{ $row['Payment Method'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Customer Phone Number'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Payment Amount'] }}</td>
                            {{--                                <td class="border p-4 border-neutral-200 dark:border-neutral-700">--}}
                            {{--                                    <button wire:click="openModal({{ json_encode($row) }})" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-xs p-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">--}}
                            {{--                                        Validar--}}
                            {{--                                    </button>--}}
                            {{--                                </td>--}}
                        </tr>
                    @endforeach
                @else
                    <tr class="justify-center">
                        <td colspan="6" class="border p-4 border-gray-300 dark:border-gray-700 text-center">Sin
                            información para mostrar
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
</div>

@section('scripsMetlife')
    <script>
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
                console.log(event[0]);
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
                console.log(event[0]);
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
    </script>
@endsection


