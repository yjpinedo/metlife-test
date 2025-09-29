<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use function Symfony\Component\Translation\t;

class DashboardController extends Controller
{

    /**
     * @throws ConnectionException
     */
    public function index()
    {
        $data = [];
        $response = Http::withHeaders([
            'X-MC-USER-AGENT' => 'eyJpcCI6IjE5Mi4xNjguMS40IiwidXNlckFnZW50IjoiTWlDbGFyb0FwcC8wLjAuMSAoT1BQTzsgQ1BIMjYyNTsgPGFuZHJvaWQvMTU+KSJ9',
            'X-MC-DEVICE-ID' => 'Y61lbnDt4jkbK6vqMTWICKXfBHTSVtgmxSJ93Vy8SAgkfT6+F49RQnms+U4HOfqnyeXkJsxXKaYjAzkcLq8owIlMyVSS\/xjdrQzavoWQXXX+f82cMKsGNRCSmMtqvkJQSrhgOaml2ehh3aGwZBBDn\/b7QYRELFQAhHKhHgzgZUs=',
            'X-ACCESS-TOKEN' => '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',
            'X-ACCESS-ENC' => '1',
            'X-MC-MAIL' => 'dany_97_@hotmail.es',
            'X-MC-APP-V' => '18.3.3',
            'Content-Type' => 'Y61lbnDt4jkbK6vqMTWICKXfBHTSVtgmxSJ93Vy8SAgkfT6+F49RQnms+U4HOfqnyeXkJsxXKaYjAzkcLq8owIlMyVSS\/xjdrQzavoWQXXX+f82cMKsGNRCSmMtqvkJQSrhgOaml2ehh3aGwZBBDn\/b7QYRELFQAhHKhHgzgZUs='
        ])->post('https://backmiclarodev5.miclarodeveloparo.claro.com.co/M3/Compartidos/Metlife/', [
            "data" => [
                "type" => "Q",
                "metlifeId" => "",
                "metlifeDate" => "2025-09-01",
                "metlifeDateTo" => "2025-09-26",
                "metlifeReq" => ""
            ]
        ]);

        if (!$response->ok()) {
            return view('dashboard', [
                'data' => [
                    'error' => $response->status(),
                    'data' => $response->json()
                ]
            ]);
        }

        if (!isset($response->json()['error']) && !$response->json()['error'] == 0) {
            return view('dashboard', [
                'data' => [
                    'error' => $response->status(),
                    'data' => $response->json()
                ]
            ]);
        }

        if (!isset($response->json()['response'])) {
            return view('dashboard', [
                'data' => [
                    'error' => $response->status(),
                    'data' => $response->json()
                ]
            ]);
        }

        if (!isset($response->json()['response']['total'])) {
            return view('dashboard', [
                'data' => [
                    'error' => $response->status(),
                    'data' => $response->json()
                ]
            ]);
        }

        if (!$response->json()['response']['total'] > 0) {
            return view('dashboard', [
                'data' => [
                    'error' => $response->status(),
                    'data' => $response->json()
                ]
            ]);
        }

        if (!isset($response->json()['response']['policies'])) {
            return view('dashboard', [
                'data' => [
                    'error' => $response->status(),
                    'data' => $response->json()
                ]
            ]);
        }

        foreach ($response->json()['response']['policies'] as $policy) {
            $data[] = [
                'date_entry' => Carbon::parse($policy['ml_date_entry'])->format('Y-m-d'),
                'id_tx' => $policy['ml_id_tx'],
                'rq_policy' => $policy['ml_rq_policy'],
                'rq_policy_format' => json_decode($policy['ml_rq_policy'], true),
            ];
        }

        return view('dashboard', ['data' =>
            [
                'error' => $response->status(),
                'data' => $data,
                'total' => $response->json()['response']['total']
            ]
        ]);
    }
}
