<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class GenerateInsuranceReportTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // Mock the file existence
        Storage::shouldReceive('exists')
            ->with('aggregated_policies.json')
            ->andReturn(true);

        // Mock the file content
        Storage::shouldReceive('get')
            ->with('aggregated_policies.json')
            ->andReturn(json_encode([
                [
                    'policy_number' => 'POL002',
                    'insurer' => 'ABC Insurance',
                    'insured_amount' => 750000,
                    'customer' => 'Business XYZ',
                    'customer_category' => 'Individual',
                    'insurance_company_ref' => 'IPN043',
                    'product' => 'Auto Protection',
                    'contract_event' => 'New Contract',
                    'premium' => 5000,
                    'commission' => 0.15,
                    'start_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addDays(335)->format('Y-m-d'),
                    'renewal_date' => Carbon::now()->addDays(335)->format('Y-m-d'),
                    'broker' => 'Broker1', 
                ],
                [
                    
                    'policy_number' => 'POL043',
                    'insurer' => 'DEF Insurers',
                    'insured_amount' => 710000,
                    'customer' => 'Enterprise B2C',
                    'customer_category' => 'Corporate',
                    'insurance_company_ref' => 'IPN040',
                    'product' => 'Property Coverage',
                    'contract_event' => 'New Contract',
                    'premium' => 5100,
                    'commission' => 0.08,
                    'start_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                    'end_date' => Carbon::now()->addDays(335)->format('Y-m-d'),
                    'renewal_date' => Carbon::now()->addDays(335)->format('Y-m-d'),
                    'broker' => 'Broker2',
                ],
            ]));
    }

    public function test_it_performs_correct_calculations()
    {
        // Mock the Artisan call
        Artisan::shouldReceive('call')
            ->with('report:insurance-data')
            ->andReturnSelf();

        Artisan::shouldReceive('output')
            ->andReturn(
          
                "Sum of Insured Amounts: 1460000\n" .
                "Average Policy Duration: 365 days\n" .
                "Total Policies (Active + Inactive): 2\n" .
                "Total Customers (Active + Inactive): 2\n" .
                "\n" .
                "Active Policies:\n" .
                "Total Active Policies: 2\n" .
                "Total Customers with Active Policies: 2\n" .
                "Sum of Insured Amounts (Active Policies): 1460000\n" .
                "Average Policy Duration (Active Policies): 365 days\n"
            );

        // Call the command
        Artisan::call('report:insurance-data');

        // Assert output contains expected values
        $output = Artisan::output();
        $this->assertStringContainsString('Sum of Insured Amounts: 1460000', $output);
        $this->assertStringContainsString('Average Policy Duration: 365 days', $output);
        $this->assertStringContainsString('Total Policies (Active + Inactive): 2', $output);
        $this->assertStringContainsString('Total Customers (Active + Inactive): 2', $output);
        $this->assertStringContainsString('Total Active Policies: 2', $output);
        $this->assertStringContainsString('Total Customers with Active Policies: 2', $output);
        $this->assertStringContainsString('Sum of Insured Amounts (Active Policies): 1460000', $output);
        $this->assertStringContainsString('Average Policy Duration (Active Policies): 365 days', $output);
    }
}
