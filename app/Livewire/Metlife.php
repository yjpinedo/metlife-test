<?php

namespace App\Livewire;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use JetBrains\PhpStorm\NoReturn;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Metlife extends Component
{
    use WithPagination, WithFileUploads;

    public $orders;
    public $order;
    public $originalData;

    public $csvFile;
    public $policeFile;
    public $csvData;

    public $MetlifeDate;

    #[NoReturn]
    public function updatedMetlifeDate(): void
    {
        if ($this->MetlifeDate != null || $this->MetlifeDate != '') {
            $this->orders = $this->getOrdersMetlife($this->MetlifeDate);
            if (count($this->orders) <= 0) {
                $this->responseInformationAlert('clear-order-date', 'Información', 'No hay órdenes registradas para el día de hoy. Por favor, selecciona otra fecha', 'question');
            }
        }

        if ($this->MetlifeDate == '') {
            $this->csvData = null;
            $this->orders = [];
        }
    }

    public function updatedCsvFile(): void
    {
        $tempArray = [];
        $this->validate([
            'csvFile' => 'required|mimes:csv,txt|max:2048',
        ]);

        $path = $this->csvFile->getRealPath();
        $this->csvData = $this->readCsv($path);

        if (count($this->csvData->all()) > 0 && count($this->orders) > 0) {
            foreach ($this->csvData->all() as $row) {
                foreach ($this->orders as $order) {
                    if ($order['code'] == $row['Sales Order Code']) {
                        if ($row['Sales Order Status'] === 'Completed') {
                            $row['Original Order Price'] = $this->formatPrice($row['Original Order Price'], 0, -3);
                            $row['Payment Amount'] = $this->formatPrice($row['Payment Amount'], 0, -3);
                            $row['Customer Full Name'] = trim($order['nameCustomer']);
                            $row['Customer Document'] = $order['documentCustomer'];
                            $row['Metlife Service Data'] = $order['data'];
                            $tempArray[] = $row;
                        }
                    }
                }
            }
        }

        if (count($tempArray) == 0) {
            $this->responseInformationAlert('clear-order-file', 'Información', 'No hay órdenes registradas para el archivo seleccionado. Por favor, selecciona otro', 'question');
        }

        $this->csvData = $tempArray;
        $this->originalData = $tempArray;
    }


    #[NoReturn]
    public function updatedPoliceFile(): void
    {
        $this->responseInformationAlert('pending-functionality', 'Información', 'Está funcionalidad aún no esta disponible', 'question');
//        $tempArray = [];
//        $this->validate([
//            'policeFile' => 'required|mimes:xlsx,xls|max:2048',
//        ]);
//
//        $path = $this->policeFile->getRealPath();
//        $polices = $this->readExcel($path);
//
//        if (count($polices->toArray()) > 0 && count($this->csvData) > 0) {
//            foreach ($polices as $police) {
//                $police['coverage_amount'] = $this->formatPrice($police['Coverage Amount'], 0, -6);
////                foreach ()
//                $tempArray[] = $police;
//            }
//        }
//
//        dd($tempArray, $this->csvData);
    }

    public function readCsv($filePath): Collection
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

    private function readExcel($filePath)
    {
        $collection = Excel::toCollection((object)null, $filePath)->first();
        $headers = $collection->first()->toArray();
        $rows = $collection->skip(1);

        return $rows->map(function ($row) use ($headers) {
            return array_combine($headers, $row->toArray());
        });
    }

    public function render(): Factory|View|\Illuminate\View\View
    {
        return view('livewire.metlife', ['csvData' => $this->csvData]);
    }

    public function getOrdersMetlife($date): array
    {
        $fullOrders = [];
        $response = $this->httpRequest(
            'https://backmiclarodev5.miclarodeveloparo.claro.com.co/M3/Compartidos/Metlife/',
            [
                'X-MC-USER-AGENT' => 'eyJpcCI6IjE5Mi4xNjguMS40IiwidXNlckFnZW50IjoiTWlDbGFyb0FwcC8wLjAuMSAoT1BQTzsgQ1BIMjYyNTsgPGFuZHJvaWQvMTU+KSJ9',
                'X-MC-DEVICE-ID' => 'Y61lbnDt4jkbK6vqMTWICKXfBHTSVtgmxSJ93Vy8SAgkfT6+F49RQnms+U4HOfqnyeXkJsxXKaYjAzkcLq8owIlMyVSS\/xjdrQzavoWQXXX+f82cMKsGNRCSmMtqvkJQSrhgOaml2ehh3aGwZBBDn\/b7QYRELFQAhHKhHgzgZUs=',
                'X-ACCESS-TOKEN' => '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',
                'X-ACCESS-ENC' => '1',
                'X-MC-MAIL' => 'dany_97_@hotmail.es',
                'X-MC-APP-V' => '18.3.3',
                'Content-Type' => 'Y61lbnDt4jkbK6vqMTWICKXfBHTSVtgmxSJ93Vy8SAgkfT6+F49RQnms+U4HOfqnyeXkJsxXKaYjAzkcLq8owIlMyVSS\/xjdrQzavoWQXXX+f82cMKsGNRCSmMtqvkJQSrhgOaml2ehh3aGwZBBDn\/b7QYRELFQAhHKhHgzgZUs='
            ],
            [
                "data" => [
                    "type" => "Q",
                    "metlifeId" => "",
                    "metlifeDate" => $date,
                    "metlifeDateTo" => $date,
                    "metlifeReq" => ""
                ]
            ],
            'POST'
        );

        $orders = $response->json();

        if (!isset($orders['error']) && !$orders['error'] == 0) {
            return $fullOrders;
        }

        if (!isset($orders['response']) && !count($orders['response']) > 0) {
            return $fullOrders;
        }

        if (!isset($orders['response']['policies']) && !count($orders['response']['policies']) > 0) {
            return $fullOrders;
        }

        foreach ($orders['response']['policies'] as $policy) {
            $data = json_decode($policy['ml_rq_policy'], true);
            $fullOrders[] = [
                'code' => $policy['ml_id_tx'],
                'nameCustomer' => $this->getFullName($data['data']['Customer']),
                'documentCustomer' => $data['data']['Customer']['IdNumber'],
                'data' => $data,
            ];
        }

        return $fullOrders;
    }

    public function httpRequest($url, $headers, $request, $method): PromiseInterface|Response
    {
        return match (true) {
            $method === 'POST' => Http::withHeaders($headers)->post($url, $request)
        };
    }

    public function getFullName($customer): string
    {
        return trim($customer['Name'] . ' ' . $customer['Surname']);
    }

    public function responseInformationAlert($name, $title, $description, $icon): void
    {
        $this->dispatch($name, [
            'title' => $title,
            'text' => $description,
            'icon' => $icon,
        ]);
    }

    public function formatPrice($price, $start, $length): string
    {
        return substr($price, $start, $length);
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
