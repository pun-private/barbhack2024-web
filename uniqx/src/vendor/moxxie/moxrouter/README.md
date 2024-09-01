MoxRouter
=========

A super simple and fast PHP router

## Installation

It's recommended that you use [Composer](https://getcomposer.org/) to install MoxRouter.

```bash
$ composer require moxxie/moxrouter "^0.2.0"
```
## Usage
### Getting started

Create an index.php file with the following contents:

```php
<?php
require 'vendor/autoload.php';
 
$router = new Moxxie\MoxRouter();
 
$router->get('/{message}', function($message){
  echo "Hello " . $message . "!";
});
 
$router->run();
```

You can test this using the built-in server that comes with PHP:
```bash
$ php -S localhost:8000
```

http://localhost:8000/world will now display "Hello, world!".

### Routing your requests
MoxRouter supports GET, POST, PUT, PATCH, and DELETE HTTP requests
```php
// Will only match GET HTTP requests
$router->get('/product/{id}', function($id){
  // Return product with id = $id
});
 
// Will only match POST HTTP requests
$router->post('/product', function(){
  // Create new a product
});
 
// Will only match PUT HTTP requests
$router->put('/product/{id}', function($id){
  // Update product with id = $id
});
 
// Will only match PATCH HTTP requests
$router->patch('/product/{id}', function($id){
  // Apply changes made to product with id = $id
});
 
// Will only match DELETE HTTP requests
$router->delete('/product/{id}', function($id){
  // Delete product with id = $id
});
 
```

### Service Container
```php
<?php
$router = new Moxxie\MoxRouter();
  
// Create an empty container
$container = [];
 
// Add a service to the container
$container['service'] = function(){
  return new Service();
};

$router->get('/', function(){
  // Use the new Service
  $service = $this->service();
});

// Run the router with the container
$router->run($container);
```

### Hooks
Before route
```php
$router->before(function(){
  // This code will be executed before a route has been executed
});
```
After route
```php
$router->after(function(){
  // This code will be executed after a route has been executed
});
```

### Not found(404)
You can override the default 404 handler
```php
$router->notFound(function(){
  // This code will be executed when a route is not found
});
```

### Rewrite all requests to MoxRouter
Apache .htaccess
```apache
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule . index.php [L]
```
Nginx
```nginx
try_files $uri /index.php;
```

## License

(MIT License)

Copyright (c) 2017 Moxxie - https://github.com/Moxxie/MoxRouter

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
