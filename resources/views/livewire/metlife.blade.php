<div>
    <div
        class="relative h-full flex-1 overflow-auto rounded-xl border border-neutral-200 dark:border-neutral-700 p-4">

        <div
            class="relative flex-1 overflow-auto border border-neutral-200 dark:border-neutral-700 p-4">
            <div class="grid gap-6 sm:grid-cols-4">
                <div class="">
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
                @if ($csvData)
                    <div>
                        <select wire:model.live="state"
                                class="ms-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">

                            <option value="all">Estado Orden</option>
                            <option value="Completed">Completado</option>
                            <option value="Canceled">Cancelado</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>

        <div class="w-full mt-2">
            <table class="border-collapse border border-neutral-200 dark:border-neutral-700 w-full">
                <thead>
                <tr>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Código</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Fecha orden</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Nombre</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Método de pago</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Teléfono Cliente</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Valor pagado</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Estado Orden</th>
                    <th class="border p-4 border-neutral-200 dark:border-neutral-700">Metlife</th>
                </tr>
                </thead>
                <tbody>
                @if ($csvData)
                    @foreach ($csvData as $row)
                        <tr>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Sales Order Code'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Sales Order Date'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Order Source Name'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700 text-center">{{ $row['Payment Method'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Customer Phone Number'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700">{{ $row['Payment Amount'] }}</td>
                            <td class="border p-4 border-neutral-200 dark:border-neutral-700 text-center">
                                @if($row['Sales Order Status'] == 'Completed')
                                    <span
                                        class="bg-green-100 text-green-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-green-900 dark:text-green-300">{{ $row['Sales Order Status'] }}</span>
                                @else
                                    <span
                                        class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded-full dark:bg-red-900 dark:text-red-300">{{ $row['Sales Order Status'] }}</span>
                                @endif
                            </td>
                            @if($row['Sales Order Status'] == 'Completed')
                                <td class="border p-4 border-neutral-200 dark:border-neutral-700">
                                    <button wire:click="openModal({{ json_encode($row) }})" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-xs p-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">
                                        Validar
                                    </button>
                                </td>
                            @else
                                <td class="border p-4 border-neutral-200 dark:border-neutral-700"></td>
                            @endif
                        </tr>
                    @endforeach
                @else
                    <tr class="justify-center">
                        <td colspan="7" class="border p-4 border-gray-300 dark:border-gray-700 text-center">Sin
                            información para mostrar
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>


        {{--<table class="border-collapse border border-gray-400 dark:border-gray-500 w-full">
            <thead>
            <tr>
                <th class="border p-4 border-gray-300 dark:border-gray-600">Fecha</th>
                <th class="border p-4 border-gray-300 dark:border-gray-600">Código</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $key => $item)
                <tr>
                    <td class="border p-4 border-gray-300 dark:border-gray-700">{{ $item['date_entry'] }}</td>
                    <td class="border p-4 border-gray-300 dark:border-gray-700">{{ $item['id_tx'] }}</td>
                </tr>
            @empty
            @endforelse
            </tbody>
        </table>
        <div class="flex py-3 justify-end">
            <button
                class="flex items-center justify-center px-3 h-8 me-3 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                wire:click="previousPage" @disabled($items->onFirstPage())>Anterior
            </button>
            <span
                class="me-2.5 my-2 text-sm text-gray-700 dark:text-gray-400">Página {{ $items->currentPage() }} de {{ $items->lastPage() }}</span>
            <button
                class="flex items-center justify-center px-3 h-8 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-lg hover:bg-gray-100 hover:text-gray-700 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
                wire:click="nextPage" @disabled(!$items->hasMorePages())>Siguiente
            </button>
        </div>--}}

        @if ($showModal)
            <div class="fixed inset-0 bg-gray-0 bg-opacity-0 flex items-center justify-center">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <h2 class="text-xl text-black font-bold mb-4">Título del Modal</h2>
                    <textarea class="mb-4 text-black">Cuerpo del modal</textarea>
                    <button wire:click="closeModal" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                        Cerrar
                    </button>
                </div>
            </div>
        @endif
    </div>

</div>


