# My thoughts on code

## Booking Controller
### general 
I would "property promotion" when when initializing BookingRepository to reduce the boiler plate code and reduce the line of code.
I would inject interface instead of concrete class in the controller in case in future we want some other implementation of BookingRepo, we will just have to change the interface binding to new class.
I would rename "$repository" to some thing more specific like "$bookingRepo" for code clarity especially when multiple repos get injected into controller.
Are all routes inside auth?
No Error handling.
If we are wrapping every response in response function I would extract this code to a middlware that callsinstead of doing it on every function.
I would use type hinting in function arguments etc for better IDE support and code understanding
why initializing $response variable just return the data if no further execution required.
No validation of user data anywhere that I can see.
Don't pass request or array to repository function for better and easier usuability of the function from some other controller. Instead only pass the data that function needs.
I would also check for the type when doing comparison "===" instead of "==".
### index function
Weird logic to return jobs. I would create a separate controller for admin data with separate routes and a middlware that check authorization criteria like user_type etc. "IsAdmin" middlware.
Move admin code to another controller.
Remove first if and do validation in a separate file "AllBookingRequest.php".
No Default value in env() variables.
Use config instead of env directly. Also set default values in config file, don't litter business code with default values.

### update function
don't use array_except explicitly whitelist column that we want to update

## Booking Repo
### general 
Almost all repo function are doing too much for my taste.
I would move most of the business logic into a service class and use repos only for the queries.
functions like "convertToHoursMins" does not belong in a repository. should be in some helper class or ideally we should be laravel's $casts if we are fetching data from DB.
We should not be setting created_at, updated_at manually when creating jobs. 
We should be using DB::transaction to ensure data integrity when adding rows to multiple tables.
If we are using repository pattern, I would prefer to find a user 
