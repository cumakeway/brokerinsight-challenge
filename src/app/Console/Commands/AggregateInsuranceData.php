<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AggregateInsuranceData extends Command
{
    protected $signature = 'aggregate:insurance-data';
    protected $description = 'Aggregate insurance data from multiple brokers';

    public function handle()
    {
        // Ingest Datae
        $broker1Data = $this->readCsv(storage_path('app/public/broker1.csv'));
        $broker2Data = $this->readCsv(storage_path('app/public/broker2.csv'));

        // Normalise Data
        $broker1Normalized = $this->normalizeData($broker1Data, 'Broker1');
        $broker2Normalized = $this->normalizeData($broker2Data, 'Broker2');

        // Aggregate Data
        $allPolicies = array_merge($broker1Normalized, $broker2Normalized);

        // Save normalized policies for reporting command
        Storage::put('aggregated_policies.json', json_encode($allPolicies));

        $this->info("Data aggregation completed and saved for reporting.");
    }

    public function readCsv($filePath)
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle, 1000, ',');
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $data[] = array_combine($headers, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    public function normalizeData($data, $brokerName)
    {
        return array_map(function ($policy) use ($brokerName) {

            $startDateRaw = trim($policy['StartDate'] ?? $policy['InitiationDate']);
            $endDateRaw = trim($policy['EndDate'] ?? $policy['ExpirationDate']);
            $renewalDateRaw = trim($policy['RenewalDate'] ?? $policy['NextRenewalDate']);

            $startDate = $this->validateDate($startDateRaw, 'd/m/Y');
            $endDate = $this->validateDate($endDateRaw, 'd/m/Y');
            $renewalDate = $this->validateDate($renewalDateRaw, 'd/m/Y');

            return [
                'policy_number' => $policy['PolicyNumber'] ?? $policy['PolicyRef'],
                'insurer' => $policy['Insurer'] ?? $policy['Underwriter'],
                'insured_amount' => (float) ($policy['InsuredAmount'] ?? $policy['CoverageAmount']),
                'customer' => $policy['CompanyDescription'] ?? $policy['BusinessDescription'],
                'customer_category' => $policy['ConsumerCategory'] ?? $policy['ClientType'],
                'insurance_company_ref' => $policy['InsurerPolicyNumber'] ?? $policy['InsuranceCompanyRef'],
                'product' => $policy['Product'] ?? $policy['InsurancePlan'],
                'contract_event' => $policy['ContractEvent'] ?? $policy['BusinessEvent'],
                'premium' => $policy['Premium'] ?? $policy['CoverageCost'],
                'commission' => $policy['Commission'] ?? $policy['BrokerFee'],
                'start_date' => $startDate,
                'end_date' => $endDate,
                'renewal_date' => $renewalDate,
                'broker' => $brokerName,
            ];
        }, $data);
    }

    private function validateDate($date, $format = 'd/m/Y')
    {
        if (!$date) {
            return null;
        }
    
        try {
            $parsedDate = Carbon::createFromFormat($format, $date);
            return $parsedDate->format('Y-m-d'); 
        } catch (\Exception $e) {
            Log::warning("Invalid date: $date");
            return null;
        }
    }
}

