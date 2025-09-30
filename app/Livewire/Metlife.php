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

        if (count($this->csvData->all()) > 0) {
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
        $headers = array_shift($rows);

        $requiredHeadings = [
            'Sales Order Code',
            'Sales Order Date',
            'Sales Order Status',
            'Original Order Price',
            'Discount Price',
            'Platform Coupon',
            'Final Order Price',
            'Sales Order Type',
            'Sale Order Source',
            'Order Source Name',
            'Merchant Name',
            'Merchant Code',
            'Payment Method',
            'Payment Status',
            'Payment Time',
            'Payment Amount',
            'Customer Phone Number'
        ];

        $headersDiff = array_diff($headers, $requiredHeadings);

        if (count($headersDiff) > 0) {
            return collect();
        }

        return collect($rows)->map(function ($row) use ($headers) {
            return array_combine($headers, $row);
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
        $response = Http::withHeaders($order['headers'])->post($order['url'], $order['data']);
//        $response = Http::withHeaders([
//            'APPLICATION_KEY' => 'Y2xhcm9xc3xhc1lANVBuZzJYU0hDcUpMag==',
//            'Content-Type' => 'application/json',
//            'Cookie' => 'platform_quickstart_tokend2d20779-f0e1-4ef3-b0fa-9cbb095542fd=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJodHRwOi8vc2NoZW1hcy54bWxzb2FwLm9yZy93cy8yMDA1LzA1L2lkZW50aXR5L2NsYWltcy9uYW1lIjoiY2xhcm9xcyIsImh0dHA6Ly9zY2hlbWFzLnhtbHNvYXAub3JnL3dzLzIwMDUvMDUvaWRlbnRpdHkvY2xhaW1zL25hbWVpZGVudGlmaWVyIjoiZDJkMjA3NzktZjBlMS00ZWYzLWIwZmEtOWNiYjA5NTU0MmZkIiwiaHR0cDovL3NjaGVtYXMubWljcm9zb2Z0LmNvbS93cy8yMDA4LzA2L2lkZW50aXR5L2NsYWltcy9ncm91cHNpZCI6IjA3MDAwZjE5LWY0MjMtNGY1Ni05YTNiLWVjYmMzNzljNDk2MSIsIm5iZiI6IjE3NTkyNjQyNjAiLCJleHAiOjE3NTkyODU4NTksInVzZXJoYXNoIjoiS2RGVnB3ZzhEQnF5S0NCZzNDYWRpdEZQSlFhYXdaNklDWkl0ZUF5V05KR2JRbW9oeTRGeVlacWFvM3l4RGZWUzlmSHFsemNUODN5RGVoTE1QcmlDOGlIRGpDbUs0V2pmaHFScEZXQmI0VEFrN3hkSXZNWFhIRzFBR2lFTGRyYnNVeWowUWJ2V3d6RTVTRzc0enplMnhJcGVBWW1UY0RzNHk3THcwdVhndWlURUd3Vk5lZmVnZXpzQUw3aE5TQjJNdnN2Z25MbDNyTkVQY2xtM3phSk15SVZiako1enZiaDFEcWJQMnppN0FydVFYVGtaTFZBNWUwWWxCclpBcXdHbyIsIm9yaWdpbiI6IlFTIiwiY29uZmlndXJhdGlvbl9kYiI6IiIsInRlbmFudF9hZG1pbmlzdHJhdG9yaWQiOiJlYTBlMDhlMy00OGVhLTRhM2QtOWY4Zi0xYmY4ZDJmNTAyNGMiLCJodHRwOi8vc2NoZW1hcy5taWNyb3NvZnQuY29tL3dzLzIwMDgvMDYvaWRlbnRpdHkvY2xhaW1zL3JvbGUiOiJPcGVyYXRpb25zIiwiaHR0cDovL3NjaGVtYXMueG1sc29hcC5vcmcvd3MvMjAwNS8wNS9pZGVudGl0eS9jbGFpbXMvc3lzdGVtIjoiZGVsaXZlcnkifQ.AO4CDdCSow97Snrc8NkqCmuBmTBXG8rs7aRjUDMhPFs' // mismo que en cURL
//        ])->withOptions(['debug' => true])
//            ->post('https://platform-proxy-testing.klimber.com/claroenrollpolicy', array(
//                'ExternalCode' => '',
//                'Customer' =>
//                    array(
//                        'ExternalClientCode' => '',
//                        'Name' => 'Nestor Ivan',
//                        'Surname' => 'Castellanos',
//                        'SecondSurname' => 'Laiton',
//                        'SocialName' => 'nombre social',
//                        'Nationality' => 'CO',
//                        'Email' => 'n.nestel@hotmail.com',
//                        'Birthday' => '2007-09-17',
//                        'IdentificationType' => 1,
//                        'IdentificationIssueDate' => '2025-09-17',
//                        'IdentificationExpirationDate' => '2030-01-31',
//                        'CivilStatus' => 1,
//                        'IdNumber' => '1068954157',
//                        'Gender' => 2,
//                        'Profession' => NULL,
//                        'Address' =>
//                            array(
//                                0 =>
//                                    array(
//                                        'AddressType' => 0,
//                                        'Street' => 'El Dorado',
//                                        'StreetNumber' => '103',
//                                        'HouseNumber' => '',
//                                        'Floor' => NULL,
//                                        'Apartment' => NULL,
//                                        'Neighborhood' => '',
//                                        'City' => 'Bogotá D.C.',
//                                        'Province' => 'Bogotá D.C.',
//                                        'ZipCode' => '110911',
//                                        'Country' => 'CO',
//                                        'IsDefault' => true,
//                                    ),
//                            ),
//                        'Phone' =>
//                            array(
//                                0 =>
//                                    array(
//                                        'PhoneType' => 3,
//                                        'PhoneCode' => '57',
//                                        'PhoneNumber' => '3114724539',
//                                    ),
//                            ),
//                    ),
//                'Package' =>
//                    array(
//                        'issueDate' => '2025-09-17T18:50:18',
//                        'startDate' => '2025-09-17T18:50:18',
//                        'endDate' => '2025-10-17T00:00:00',
//                        'Products' =>
//                            array(
//                                0 =>
//                                    array(
//                                        'Code' => 'claro_proteccion_vital',
//                                        'Option' => '1',
//                                        'ProductAmount' => 20000000,
//                                        'Coverage' =>
//                                            array(
//                                                0 =>
//                                                    array(
//                                                        'Code' => 'accidental_death',
//                                                        'CoverageAmount' => 20000000,
//                                                    ),
//                                                1 =>
//                                                    array(
//                                                        'Code' => 'funeral_individual',
//                                                        'CoverageAmount' => 5000000,
//                                                    ),
//                                                2 =>
//                                                    array(
//                                                        'Code' => 'disability_accidental',
//                                                        'CoverageAmount' => 20000000,
//                                                    ),
//                                            ),
//                                    ),
//                            ),
//                    ),
//                'Payment' =>
//                    array(
//                        'PaymentType' => 7,
//                        'ExternalCode' => '01J2P6JG4WDSQ21T1NKPFA05E2',
//                        'Date' => '2025-09-17',
//                        'Amount' => 5000,
//                        'Installment' => 1,
//                        'Status' => 2,
//                    ),
//                'Pricing' =>
//                    array(
//                        'Markup' => 0,
//                        'Commission' => 0,
//                        'Premium' => 5000,
//                    ),
//            ));

        return $response->json();
    }
}
