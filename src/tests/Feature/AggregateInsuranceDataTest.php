<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AggregateInsuranceDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Mock file storage for CSV data
        Storage::fake('public');

        Storage::put('broker1.csv', "PolicyNumber,Insurer,InsuredAmount,BusinessDescription,ClientType,InsurerPolicyNumber,Product,BusinessEvent,Premium,Commission,StartDate,EndDate,RenewalDate\POL001,ABC Insurance,1000000,Business ABC,Corporate,IPN005,Property Insurance,New Business,8000,0.15,05/01/2025,05/01/2026,05/01/2026");

        Storage::put('broker2.csv', "PolicyRef,Underwriter,CoverageAmount,CompanyDescription,ConsumerCategory,InsuranceCompanyRef,InsurancePlan,ContractEvent,CoverageCost,BrokerFee,InitiationDate,ExpirationDate,NextRenewalDate\POL044,DEF Insurers,940000,Business GHI,Individual,ICR054,Home Secure,New Contract,6600,0.19,20/02/2023,20/02/2024,20/02/2024");
    }

    //Ensures that the aggregate:insurance-data command completes successfully and creates the expected file
    public function test_aggregate_insurance_data_command_creates_normalized_file()
    {
        $this->artisan('aggregate:insurance-data')
            ->expectsOutput('Data aggregation completed and saved for reporting.')
            ->assertExitCode(0);

        Storage::assertExists('aggregated_policies.json');
    }

    //Confirms that the normalizeData function correctly transforms data into the normalized format
    public function test_normalize_data_function_returns_expected_format()
    {
        $command = new \App\Console\Commands\AggregateInsuranceData();
        $data = [
            [
                'PolicyNumber' => 'POL001',
                'Insurer' => 'ABC Insurance',
                'InsuredAmount' => 1000000,
                'BusinessDescription' => 'Business ABC',
                'ClientType' => 'Corporate',
                'InsurerPolicyNumber' => 'IPN005',
                'Product' => 'Property Insurance',
                'BusinessEvent' => 'New Business',
                'Premium' => 8000,
                'Commission' => 0.15,
                'StartDate' => '05/01/2025',
                'EndDate' => '05/01/2026',
                'RenewalDate' => '05/01/2026'
            ]
        ];

        $normalizedData = $command->normalizeData($data, 'Broker1');

        $this->assertEquals('POL001', $normalizedData[0]['policy_number']);
        $this->assertEquals('ABC Insurance', $normalizedData[0]['insurer']);
        $this->assertEquals(1000000, $normalizedData[0]['insured_amount']);
        $this->assertEquals('Business ABC', $normalizedData[0]['customer']);
        $this->assertEquals('Corporate', $normalizedData[0]['customer_category']);
        $this->assertEquals('IPN005', $normalizedData[0]['insurance_company_ref']);
        $this->assertEquals('Property Insurance', $normalizedData[0]['product']);
        $this->assertEquals('New Business', $normalizedData[0]['contract_event']);
        $this->assertEquals(8000, $normalizedData[0]['premium']);
        $this->assertEquals(0.15, $normalizedData[0]['commission']);
        $this->assertEquals('2025-01-05', $normalizedData[0]['start_date']);
        $this->assertEquals('2026-01-05', $normalizedData[0]['end_date']);
        $this->assertEquals('2026-01-05', $normalizedData[0]['renewal_date']);
     
    }

    //Validates that the readCsv function correctly parses CSV files into arrays.
    public function test_read_csv_function_reads_and_parses_data()
    {
        $command = new \App\Console\Commands\AggregateInsuranceData();
        $data = $command->readCsv(storage_path('app/public/broker1.csv'));

        $this->assertEquals('POL001', $data[0]['PolicyNumber']);
        $this->assertEquals('ABC Insurance', $data[0]['Insurer']);
        $this->assertEquals(1000000, $data[0]['InsuredAmount']);
        $this->assertEquals('Business ABC', $data[0]['BusinessDescription']);
        $this->assertEquals('Corporate', $data[0]['ClientType']);
        $this->assertEquals('IPN001', $data[0]['InsurerPolicyNumber']);
        $this->assertEquals('Property Insurance', $data[0]['Product']);
        $this->assertEquals('New Business', $data[0]['BusinessEvent']);
        $this->assertEquals(8000, $data[0]['Premium']);
        $this->assertEquals(0.15, $data[0]['Commission']);
        $this->assertEquals('15/01/2023', $data[0]['StartDate']);
        $this->assertEquals('15/01/2024', $data[0]['EndDate']);
        $this->assertEquals('15/01/2024', $data[0]['RenewalDate']);
      

    }

    //Tests the validateDate function for valid date inputs.
    public function test_validate_date_returns_valid_date()
    {
        $command = new \App\Console\Commands\AggregateInsuranceData();
        $date = $command->validateDate('01/01/2022', 'd/m/Y');

        $this->assertEquals('2022-01-01', $date);
    }

    //Tests the validateDate function for  invalid date inputs.
    public function test_validate_date_handles_invalid_date()
    {
        $command = new \App\Console\Commands\AggregateInsuranceData();
        $date = $command->validateDate('invalid-date', 'd/m/Y');

        $this->assertNull($date);
    }
}
