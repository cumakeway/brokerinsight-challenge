<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateInsuranceReport extends Command
{
    protected $signature = 'report:insurance-data';
    protected $description = 'Generate a report from aggregated insurance data';

    public function handle()
    {
        $filePath = storage_path('app/aggregated_policies.json');

        if (!Storage::exists('aggregated_policies.json')) {
            $this->error('No aggregated data found. Run the aggregation command first.');
            return;
        }

        $policies = json_decode(Storage::get('aggregated_policies.json'), true);
        $this->generateReport($policies);
    }

    public function generateReport($policies)
    {
        $now = Carbon::now();

        //calculate and display the total count of policies
        //total count of customers
        //the sum of insured amounts
        //the average policy duration across (in days) across the two brokers for active policies

        $activePolicies = array_filter($policies, function ($policy) use ($now) {
            return $policy['start_date'] <= $now && $policy['renewal_date'] >= $now;
        });

        $totalPolicies = count($policies);
        $totalActivePolicies = count($activePolicies);
        $totalCustomersWithActivePolicies = count(array_unique(array_column($activePolicies, 'customer')));
        $totalInsuredAmountOnActivePolicies = array_sum(array_column($activePolicies, 'insured_amount'));

        $totalCustomers = count(array_unique(array_column($policies, 'customer')));
        $totalInsuredAmount = array_sum(array_column($policies, 'insured_amount'));

        $averageDurationOfActivePolicies = array_sum(array_map(function ($policy) {
            return Carbon::parse($policy['start_date'])->diffInDays(Carbon::parse($policy['renewal_date']));
        }, $activePolicies)) / max($totalActivePolicies, 1);

    
        $this->info("Sum of Insured Amounts: $totalInsuredAmount");
        $this->info("Average Policy Duration: $averageDurationOfActivePolicies days");


        $this->info("Total Policies (Active + Inactive): $totalPolicies");
        $this->info("Total Customers (Active + Inactive): $totalCustomers");
        $this->info("\nActive Policies:");
        $this->info("Total Active Policies: $totalActivePolicies");
        $this->info("Total Customers with Active Policies: $totalCustomersWithActivePolicies");
        $this->info("Sum of Insured Amounts (Active Policies): $totalInsuredAmountOnActivePolicies");
        $this->info("Average Policy Duration (Active Policies): $averageDurationOfActivePolicies days");

        $brokerName = $this->ask('Enter a broker name to view their policies (or press Enter to skip)');
        if ($brokerName) {
            $brokerPolicies = array_filter($policies, fn($policy) => $policy['broker'] === $brokerName);
            $this->table([
                'Policy Number', 
                'Insurer',
                'Insured Amount', 
                'Customer', 
                'Customer Category',
                'Insurance Company Ref.',
                'Product',
                'Contract Event',
                'Premium',
                'Commission',
                'Start Date', 
                'End Date',
                'Renewal Date',
                'Broker'
            ], $brokerPolicies);
        }
    }
}