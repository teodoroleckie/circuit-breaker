# PHP Circuit Breaker 

With the powerful Circuit Breaker library you will be able to manage and protect your application from requests that stop working to avoid overloads. The implementation that you will have to do is very simple.

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/teodoroleckie/circuit-breaker/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/teodoroleckie/circuit-breaker/?branch=main)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/teodoroleckie/circuit-breaker/badges/code-intelligence.svg?b=main)](https://scrutinizer-ci.com/code-intelligence)
[![Build Status](https://scrutinizer-ci.com/g/teodoroleckie/circuit-breaker/badges/build.png?b=main)](https://scrutinizer-ci.com/g/teodoroleckie/circuit-breaker/build-status/main)

It's common for software systems to make remote calls to software running in different processes, probably on different 
machines across a network. One of the big differences between in-memory calls and remote calls is that remote calls can 
fail, or hang without a response until some timeout limit is reached. What's worse if you have many callers on a 
unresponsive supplier, then you can run out of critical resources leading to cascading failures across multiple systems. 
In his excellent book Release It, Michael Nygard popularized the Circuit Breaker pattern to prevent this kind of 
catastrophic cascade.

The basic idea behind the circuit breaker is very simple. You wrap a protected function call in a circuit breaker object, 
which monitors for failures. Once the failures reach a certain threshold, the circuit breaker trips, and all further 
calls to the circuit breaker return with an error, without the protected call being made at all. 
Usually you'll also want some kind of monitor alert if the circuit breaker trips.


Circuit breaker is heavily used in microservice architecture to find issues between microservices calls.
The main idea is to protect your code from making unnecessary call if the microservice you call is down.

# Features:
- Automatic update. (i.e you don't have to manually add success or failure method like other library)
- Return result from the protected function.
- Retry timeout.
- Protect your services calls if they are dropped.

# Usage:

- You have to create an instance of the CircuitBreaker class with three arguments.
The first must be an instance of your preferred psr-16 cache, it must implement the Psr\SimpleCache\CacheInterface 
interface and is not included with this library.  
- The second required argument is the number of retries the library must do before entering the open state.  
- The third argument is the time in seconds that the circuit must wait before making new calls to the protected service.
- The CircuitBreaker class makes available the callService method in which a closure must be implemented to call the service from within.
- Within the closure, the exception that would be fired when the service is not operational must be controlled, 
  said exception is caught and a CircuitBreakerException exception is created, passing through its constructor the one that has been handled.
- At this point, the service is fully protected against failure.
- When CircuitBreaker goes to the open state, that is, the service is not functional, it will stop receiving requests 
  by throwing the last known exception in all cases until the service is available to receive new requests.
- The whole process is transparent and automatic for you.  
- Given the nature of the microservices' architecture, it is recommended to use a centralized cache storage shared between all your frontend and backend machines.

```php
<?php
require "vendor/autoload.php";

use Tleckie\CircuitBreaker\CircuitBreaker;
use Tleckie\CircuitBreaker\Exception\CircuitBreakerException;


$circuitBreaker = new CircuitBreaker(
    // Psr\SimpleCache\CacheInterface object, not included with this library.
    new SimpleCacheAdapter( ... ),
    3, // maxFailures
    60 // retryTimeout
);

// create your service instance
$service = new MyUserService( ... );

$serviceResponse = $circuitBreaker->callService(

    static function () use ($service) {
        try {

            return $service->users();

        } catch (ServiceTimeOutException $exception) {
            throw new CircuitBreakerException($exception);
        }

    },
    'my.user.service'
);

```
