<?php

namespace App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Metlife extends Component
{
    use WithPagination, WithFileUploads;

    public $orders;
    public $order;
    public $state;
    public $originalData;

    public $page = 1; // página actual
    public $perPage = 5; // cantidad por página

    public $csvFile;
    public $csvData;

    public function mount($data): void
    {
        $this->orders = $data;
    }

    public function updatedCsvFile(): void
    {
        $tempArray = [];
        $this->validate([
            'csvFile' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $this->csvFile->getRealPath();
        $this->csvData = $this->readCsv($path);

        foreach ($this->csvData->all() as $row) {
            foreach ($this->orders['data'] as $value) {
                if ($value['id_tx'] == $row['Sales Order Code']) {
                    $row['headers'] = $value['rq_policy_format']['headers'];
                    $row['data'] = $value['rq_policy_format']['data'];
                    $row['url'] = $value['rq_policy_format']['url'];
                    $tempArray[] = $row;
                }
            }
        }

        $this->csvData = $tempArray;
        $this->originalData = $tempArray;
    }

    public function updatedState(): void
    {
        if ($this->state == 'all') {
            $this->csvData = $this->originalData;
        }

        if ($this->state == 'Completed') {
            $this->csvData = $this->originalData;
            $this->csvData = array_filter($this->csvData, function ($item) {
                return $item['Sales Order Status'] == 'Completed';
            });
        }

        if ($this->state == 'Canceled') {
            $this->csvData = $this->originalData;
            $this->csvData = array_filter($this->csvData, function ($item) {
                return $item['Sales Order Status'] == 'Canceled';
            });
        }
    }

    public function readCsv($filePath): \Illuminate\Support\Collection
    {
        $rows = array_map('str_getcsv', file($filePath));
        $header = array_shift($rows);

        return collect($rows)->map(function ($row) use ($header) {
            return array_combine($header, $row);
        });
    }

    public function updatingPage(): void
    {
        $this->resetPage();
    }

    public function render(): Factory|View|\Illuminate\View\View
    {
        $paginator = new LengthAwarePaginator(
            array_slice($this->orders['data'], ($this->page - 1) * $this->perPage, $this->perPage),
            $this->orders['total'],
            $this->perPage,
            $this->page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.metlife', ['items' => $paginator, 'csvData' => $this->csvData]);
    }

    public function nextPage(): void
    {
        $this->page++;
    }

    public function previousPage(): void
    {
        $this->page = max(1, $this->page - 1);
    }

    /* Modal */
    public $showModal = false;

    public function openModal($order): void
    {
        $this->order = $order['headers'];
        $response = $this->getDataMetlife($order);
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
    }

    public function getDataMetlife($order)
    {
        //dd($order['data']);
        $response = Http::withHeaders($order['headers'])->post($order['url'], $order['data']);
        /*$response = Http::withHeaders([
            'APPLICATION_KEY' => 'Y2xhcm9xc3xhc1lANVBuZzJYU0hDcUpMag==',
            'Content-Type' => 'application/json',
        ])->post('https://platform-proxy-testing.klimber.com/claroenrollpolicy', [
            "ExternalCode" => "",
            "Customer" => [
                "ExternalClientCode" => "",
                "Name" => "Nestor Ivan",
                "Surname" => "Castellanos",
                "SecondSurname" => "Laiton",
                "SocialName" => "nombre social",
                "Nationality" => "CO",
                "Email" => "n.nestel@hotmail.com",
                "Birthday" => "2007-09-17",
                "IdentificationType" => 1,
                "IdentificationIssueDate" => "2025-09-17",
                "IdentificationExpirationDate" => "2030-01-31",
                "CivilStatus" => 1,
                "IdNumber" => "1068954157",
                "Gender" => 2,
                "Profession" => null,
                "Address" => [
                    [
                        "AddressType" => 0,
                        "Street" => "El Dorado",
                        "StreetNumber" => "103",
                        "HouseNumber" => "",
                        "Floor" => null,
                        "Apartment" => null,
                        "Neighborhood" => "",
                        "City" => "Bogotá D.C.",
                        "Province" => "Bogotá D.C.",
                        "ZipCode" => "110911",
                        "Country" => "CO",
                        "IsDefault" => true
                    ]
                ],
                "Phone" => [
                    [
                        "PhoneType" => 3,
                        "PhoneCode" => "57",
                        "PhoneNumber" => "3114724539"
                    ]
                ]
            ],
            "Package" => [
                "issueDate" => "2025-09-17T18:50:18",
                "startDate" => "2025-09-17T18:50:18",
                "endDate" => "2025-10-17T00:00:00",
                "Products" => [
                    [
                        "Code" => "claro_proteccion_vital",
                        "Option" => "1",
                        "ProductAmount" => 20000000,
                        "Coverage" => [
                            [
                                "Code" => "accidental_death",
                                "CoverageAmount" => 20000000
                            ],
                            [
                                "Code" => "funeral_individual",
                                "CoverageAmount" => 5000000
                            ],
                            [
                                "Code" => "disability_accidental",
                                "CoverageAmount" => 20000000
                            ]
                        ]
                    ]
                ]
            ],
            "Payment" => [
                "PaymentType" => 7,
                "ExternalCode" => "01J2P6JG4WDSQ21T1NKPFA05E2",
                "Date" => "2025-09-17",
                "Amount" => 5000,
                "Installment" => 1,
                "Status" => 2
            ],
            "Pricing" => [
                "Markup" => 0,
                "Commission" => 0,
                "Premium" => 5000
            ]
        ]);*/

        dd($response);

        return $response;
    }
}
