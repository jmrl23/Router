# Router

A php routing module used for easier handling of server requests.

Supports an Express-styled path string with the help of [Path-to-RegExp](https://github.com/gpolguere/path-to-regexp-php).

[View Demo Site](https://audile-cone.000webhostapp.com/)

---

## Usage

```php
# require the `mod.php` inside the `.router` folder
require_once __DIR__ . '/.router/mod.php';

# make an instance
$router = new Router\Router();

# activate the instance
# this should always be placed at the very bottom
$router->activate();
```

## Configuration
Router configuration is located at `.router/router.ini`.
<br><br>
The `.htaccess` plays an important role for this to work, don't forget to configure it corresponding to your project.
<br><br>
If your application is running on an nginx server, just do the same concept that htaccess does; redirect requests into your base file.

### Router methods

Assuming an instance is stored into a `$router` variable.

**$router->apply(string | array | Closure | router\\Router $e, ...[...]?)**
  - Responsible for adding an element into the router's stack,
  the stack is the list of registered routes, middleware, and callbacks.

  - This method is useful for making a global middleware for your application.

  - If the first parameter is a type string, it will convert into `$router->all(...)`.

  ```php
  $router->apply(function ($request, $response, $next) {
    $request->newProperty = 'something';
    $next(); # <-- this is necessary unless your application will response a timeout.
  });

  # only executes if the path is matched to the uri
  $router->apply('/', function ($request, $response, $next) { .. });
  ```
<br>

**$router->all(string $path, Closure | array $callback)**

  - Make a route for every type of request method, if the path is matched to the uri; regardless of the request method, the callback will be trigger.

  ```php
  $router->all('/example', function () {
    echo 'This is an example';
    exit;
  });

  # These calls will return exactly the same, they will output `This is an example`.

  # curl -X GET http://localhost/app/example
  # curl -X POST http://localhost/app/example
  # curl -X PUT http://localhost/app/example
  # curl -X PATCH http://localhost/app/example
  # curl -X DELETE http://localhost/app/example
  ```
<br>

**$router->get(string $path, Closure | array $callback)**

  - Callback will trigger if the method is `GET` and the path is matched to the uri.

  ```php
  $router->get('/example', function () {
    echo 'Hello, World!';
    exit;
  });
  
  # outputs `Hello, World!`.
  # curl -X GET http://localhost/app/example
  ```
<br>

**$router->post(string $path, Closure | array $callback)**

  - Callback will trigger if the method is `POST` and the path is matched to the uri.

  ```php
  $router->post('/example', function () {
    echo 'Hello, World!';
    exit;
  });
  
  # outputs `Hello, World!`.
  # curl -X POST http://localhost/app/example
  ```
<br>

**$router->put(string $path, Closure | array $callback)**

  - Callback will trigger if the method is `PUT` and the path is matched to the uri.

  ```php
  $router->put('/example', function () {
    echo 'Hello, World!';
    exit;
  });
  
  # outputs `Hello, World!`.
  # curl -X PUT http://localhost/app/example
  ```
<br>

**$router->patch(string $path, Closure | array $callback)**

  - Callback will trigger if the method is `PATCH` and the path is matched to the uri.

  ```php
  $router->patch('/example', function () {
    echo 'Hello, World!';
    exit;
  });
  
  # outputs `Hello, World!`.
  # curl -X PATCH http://localhost/app/example
  ```
<br>

**$router->delete(string $path, Closure | array $callback)**

  - Callback will trigger if the method is `DELETE` and the path is matched to the uri.

  ```php
  $router->delete('/example', function () {
    echo 'Hello, World!';
    exit;
  });
  
  # outputs `Hello, World!`.
  # curl -X DELETE http://localhost/app/example
  ```
### Callback structure

A callback used into the router method has 3 parameters.

```php
function ($request, $response, $next) {
  // do some stuff
}
```
- `request` has the information about the client's request to the server.
- `response` has the methods on how will you response to the client.
- `next` is a function that tells the router to proceed to the next item in its `stack`. 

### Response methods

**$response->header(string $key, string $value)**

  - Set response header.

  ```php
  $response->header('X-My-Header', 'some value');
  ```
<br>

**$response->removeHeader(string $key)**

  - Remove a header.

  ```php
  $response->removeHeader('X-My-Header');
  ```
<br>

**$response->status(int $code)**

  - Set response status code.

  ```php
  $response->status(404); // not found
  ```
<br>

**$response->end(string $text = '', string $contentType = '')**

  - End the process, response to the client.

  ```php
  $response->end('Hello, World');
  ```
<br>

**$response->text(string $text)**

  - End the process, response a text to the client.
  - content-type: `text/plain; charset=UTF-8`

  ```php
  $response->text('Hello, World');
  ```
<br>

**$response->html(string $html)**

  - End the process, response a html document to the client.
  - content-type: `text/html; charset=UTF-8`.

  ```php
  $response->html('<h1>Hello, World</h1>');
  ```
<br>

**$response->json(string | object | array $json)**

  - End the process, response a json application to the client.
  - content-type: `application/json; charset=UTF-8`.

  ```php
  $response->json('{ "message": "Hello, World!" }');
  $response->json(['message' => 'Hello, World!']);
  $response->json((object)['message' => 'Hello, World!']);
  ```
<br>

**$response->file(string $absolutePath)**

  - Response a file. Content-type is depending on file.

  ```php
  $response->file(__DIR__ . '/my-storage/test.txt');
  ```
<br>

**$response->render(string $template, array $data = [])**

  - Response templated html document.
  - content-type: `text/html; charset=UTF-8`.
  - Before using this, if you does not yet set your views directory, you must call the `$router->views(string $absolutePath)` to set it.
  - Data inside the `$data` array will be available to your template document if it's extension is `.php`.
  - Inside a template, you also can access properties of `$request` object such as `baseURL`, `method`, `headers`, etc.

  ```php
  /**
   * to set views directory
   * $router->views(__DIR__ . '/views');
   */
  $response->render('home.php', [
    'message' => 'Hello, World!'
  ]);
  ```

___
## Note
Every path parameters should always supply an `absolute path`. 
<br>
[What is absolute path?](https://www.computerhope.com/issues/ch001708.htm)
```php
# example
# instead of giving..

$path = './folder'; or $path = 'folder';

# use
$path = __DIR__ . '/folder';
```
