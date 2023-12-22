# SuperBan Laravel Package

<p align="center">
<a href="https://github.com/Mane-Olawale/superban"><img src="https://github.com/Mane-Olawale/superban/actions/workflows/tests.yml/badge.svg" alt="Github"></a>
<a href="https://packagist.org/packages/mane-olawale/superban"><img src="https://img.shields.io/packagist/dt/mane-olawale/superban" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/mane-olawale/superban"><img src="https://img.shields.io/packagist/v/mane-olawale/superban" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/mane-olawale/superban"><img src="https://img.shields.io/packagist/l/mane-olawale/superban" alt="License"></a>
</p>

The SuperBan Laravel package is a powerful tool designed to enhance your application's security and performance by efficiently managing and restricting users or clients who exceed predefined request limits. This package is particularly useful in scenarios where abusive or excessive requests can pose a threat to your application's stability and responsiveness.

## Installation

You can install the package via composer:

```bash
composer require mane-olawale/superban
```

## Configuration

This package can be used without any configuration, but you can as well change something to what you prefer.

First publish the package config to your config directory by running

```bash
php artisan vendor:publish --provider="ManeOlawale\Superban\SuperbanServiceProvider" --tag="superban.config"
```

Then add `SUPERBAN_DRIVER` to your .env like so:

```env
SUPERBAN_DRIVER=redis
```

## Usage

### Global banning middleware

```php
Route::middleware(['superban:100,3,2880'])->group(function () {
    Route::post('/audio', function () {
        // ...
    });
 
    Route::post('/video', function () {
        // ...
    });
});
```

In the provided Laravel route definition, the `superban` middleware has been applied to a group of routes, specifying the parameters `100,3,2880`. Let's break down what each of these parameters signifies:

- **`100`:** This represents the maximum number of allowed requests within the defined time frame.
- **`3`:** The second argument denotes the duration of the time frame in which the specified number of requests is allowed. In this case, it is set to 3 minutes.
- **`2880`:** The third argument defines the duration of the ban imposed on the user or client in case they exceed the allowed request limit. In this example, the ban lasts for 2880 minutes, which is equivalent to 2 days.

In summary, the `superban` middleware has been configured to enforce rate limiting on the group of routes. Users or clients are allowed a maximum of 100 requests within a 3-minute time window. If a user exceeds this limit, they will be banned for 2880 minutes (2 days). This setup helps to control and mitigate potential abuse or excessive requests, contributing to the overall security and stability of the Laravel application.

> Crucially, the `superban` middleware, when applied to an route or group, ensures that the resulting ban is universally enforced. Bellow is an example of a route specific ban.

### Route specific banning middleware

```php
Route::middleware(['superban_route:100,3,2880'])->group(function () {
    Route::post('/audio', function () {
        // ...
    });
 
    Route::post('/video', function () {
        // ...
    });
});
```
In the given Laravel route definition, the `superban_route` middleware enforces rate limiting and user banning on a per-route basis.

## Default value

If no or fewer arguments are explicitly provided for the `superban` and `superban_route` middleware, default values will be assumed.

The defaults are set to allow 200 requests within a 2-minute time frame, with a subsequent ban duration of 1440 minutes (equivalent to 1 day).

This approach ensures that the middleware is functional even when custom parameters are not explicitly defined, providing a balance between flexibility and ease of use.

## HTTP response

When a user or client is banned, a `403 Forbidden` response code is returned. The content of the response varies based on the client's expected format. If the client expects JSON, the response contains a JSON payload with a message and the ban expiration timestamp. Otherwise, a plain text response is returned.

### JSON Response for Banned Users:
```php
response()->json([
    'message' => 'Sorry, you\'re temporarily banned. Please return after Dec 22, 2023, 10:57 pm.',
    'until' => '2023-12-22 22:57:28'
], 403);
```
The `message` key provides a human-readable explanation of the ban, and the `until` key specifies the date and time until which the ban is effective. On the other hand, the text response includes a plain text message with the same ban details. This approach ensures that the response format aligns with the client's expectations, providing a clear and consistent message for users who are temporarily banned.

### Text Response for Banned Users:
```php
response(
    'Sorry, you\'re temporarily banned. Please return after Dec 22, 2023, 10:57 pm.',
    403,
    [
        'banned-until' => '2023-12-22 22:57:28'
    ]
);
```
A plain text message is provided, conveying a human-readable explanation of the ban, just like in the JSON response. Additionally, to maintain consistency with the JSON format, a custom header named 'banned-until' is included in the HTTP response headers. This header serves the same purpose as the 'until' key in the JSON response, indicating the date and time until which the ban is effective. This approach ensures that clients, regardless of their expected response format, receive clear and consistent information about the temporary ban.

### Custom Ban Response Handling in Superban

```php
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use ManeOlawale\Superban\Superban;

Superban::banResponseUsing(function (Request $request, Carbon $until, $default) {
    if (/* Check something */) {
        return response('Hands up!', 401);
    }
    return $default;
});
```

The `banResponseUsing` method in Superban allows you to customize the response that is sent when a user or client is banned. This callback function takes three parameters:

1. **`$request`:** Represents the current HTTP request. You can access information about the request, allowing for dynamic response customization based on specific conditions or request attributes.

2. **`$until`:** Indicates the date and time until which the ban is effective.

3. **`$default`:** Represents the default response that Superban would generate. This includes the default HTTP status code and content that would be sent if a custom response is not specified.

In the provided example, the custom response is a simple text response of 'Hands up!' with a `401 Unauthorized` HTTP status code. This showcases how you can completely customize the ban response to fit your application's requirements, providing flexibility in crafting messages and status codes tailored to your specific use case.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
