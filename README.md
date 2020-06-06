##### Run tests:

`vendor/bin/phpunit -c tests/phpunit.xml tests/`

##### Run example test script:

`php test.php 2020-01-01`



##### Coverage:

```
PHPUnit 9.1.5 by Sebastian Bergmann and contributors.
 
 ................                                                  16 / 16 (100%)
 
 Time: 00:01.236, Memory: 8.00 MB
 
 OK (16 tests, 18 assertions)
 
 
 Code Coverage Report:     
   2020-06-06 11:42:54     
                           
  Summary:                 
   Classes: 100.00% (3/3)  
   Methods: 100.00% (16/16)
   Lines:   100.00% (74/74)
 
 StillAlive\RateMeanCalculator\CurrencyRateMeanCalculator
   Methods: 100.00% ( 4/ 4)   Lines: 100.00% ( 17/ 17)
 StillAlive\RateMeanCalculator\Exceptions\BadProviderResponseFormatException
   Methods:  ( 0/ 0)   Lines:  (  0/  0)
 StillAlive\RateMeanCalculator\Exceptions\CurrencyRateMeanCalculatorException
   Methods:  ( 0/ 0)   Lines:  (  0/  0)
 StillAlive\RateMeanCalculator\Exceptions\IncorrectDateException
   Methods:  ( 0/ 0)   Lines:  (  0/  0)
 StillAlive\RateMeanCalculator\Exceptions\NoProvidersSpecifiedException
   Methods:  ( 0/ 0)   Lines:  (  0/  0)
 StillAlive\RateMeanCalculator\Exceptions\ServiceUnavailableException
   Methods:  ( 0/ 0)   Lines:  (  0/  0)
 StillAlive\RateMeanCalculator\Providers\CBRRateProvider
   Methods: 100.00% ( 6/ 6)   Lines: 100.00% ( 28/ 28)
 StillAlive\RateMeanCalculator\Providers\RBCRateProvider
   Methods: 100.00% ( 6/ 6)   Lines: 100.00% ( 29/ 29)
```
