**Requirements**

You need to have Docker desktop already installed on your machine

**Setup Instructions**

1. Clone repository and checkout master branch
2. From vscode or your chosen IDE open the terminal and run `docker-compose up -d` to spin up the docker container
3. run `composer install`
4. Open docker container terminal, type `bash`
5. Add csv files `broker1.csv` and `broker2.csv` to `storage/app/public` folder since this folder is ignored by git.

**How to Run Application**

The Application is a command line application with two commands `AggregateInsuranceData` and `GenerateInsuranceReport`

The `AggregateInsuranceData` command aggregates the insurance data from multiple brokers in the csv files provided, normalizes 
the data and stores them in a json file name `aggregated_policies.json`. This command is executed by running 
`php artisan aggregate:insurance-data`.

The `GenerateInsuranceReport` command generates a report from the aggregated policies stored in `aggregated_policies.json`. 
This command is executed by calling `php artisan report:insurance-data`.
The report generated contains the following; 
* `Sum of Insured Amounts`, 
* `Average Policy Duration`, 
* `Total Policies (Active + Inactive)`,
* `Total Customers (Active + Inactive)`, 
* `Total Active Policies`, 
* `Total Customers with Active Policies`,
*  `Sum of Insured Amounts (Active Policies)`
*  `Average Policy Duration (Active Policies)`


The report also displays a prompt which asks for the user to filter the reports by providing the broker name.
The broker names that can be provided for this prompt are:
`Broker1`, or `Broker2`.

**Unit Tests**

The application has two test classes `AggregateInsuranceDataTest` which covers the testing on the `AggregateInsuranceData` command and
`GenerateInsuranceReportTest` which covers testing on the `GenerateInsuranceReport` class.

You can run all the tests using `php artisan test` or individual tests by running 

`php artisan test tests/Feature/AggregateInsuranceDataTest.php`.

`php artisan test tests/Feature/GenerateInsuranceReportTest.php` 

**Side notes**

Please note that the `AggregateInsuranceData` must always be ran before calling the `GenerateInsuranceReport` otherwise an error would 
occur since there would be aggregated policies to report on.


