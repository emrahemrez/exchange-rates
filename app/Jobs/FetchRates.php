<?php

namespace App\Jobs;

use App\Models\Rate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class FetchRates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('FetchRates job started.');

        try {
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->get('https://api.nbrb.by/exrates/rates?periodicity=0');
            if ($response->getStatusCode() !== 200) {
                \Log::error('Failed to fetch rates', ['status' => $response->getStatusCode()]);
                return;
            }
            $rates = json_decode($response->getBody(), true);
            \Log::info('Rates fetched successfully.', ['rates' => $rates]);

            foreach ($rates as $rate) {
                $date = substr($rate['Date'], 0, 10);
                $record = Rate::updateOrCreate(
                    ['date' => $date, 'currency' => $rate['Cur_Abbreviation']],
                    ['rate' => $rate['Cur_OfficialRate']]
                );
                \Log::info('Rate saved or updated.', ['record' => $record]);
            }

            \Log::info('Rates successfully saved.');
        } catch (\Exception $e) {
            \Log::error('An error occurred while fetching rates', ['message' => $e->getMessage()]);
        }

        \Log::info('FetchRates job finished.');
    }
}
