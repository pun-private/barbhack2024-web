<?php
/*
 * Created by Moxxie. https://github.com/Moxxie/MoxRouter
 */
namespace Moxxie;

class MoxRouter {

  private $routes = [];

  private $hooks = ['before_route' => false, 'after_route' => false];

  private $notFound;

  public $baseUri = '/';

  private $container = [];

  public function __call($method, $arguments) {
    if(isset($this->container[$method])){
      return call_user_func_array(\Closure::bind($this->container[$method], $this), $arguments);
    }
    throw new \Exception("Call to undefined method: " . $method);
  }

  public function __get($name) {
    if(isset($this->container[$name])){
      return $this->container[$name];
    }
    throw new \Exception("Call to undefined property: " . $name);
  }

  private function add($method, $route, $function){
    if(empty($route)){
      throw new \Exception("The route can not be empty");
    }
    $this->routes[] = [
      'path' => $this->baseUri . ltrim($route, '/'),
      'function' => $function,
      'method' => $method
    ];
  }

  public function get($route, $function, $class = false){
    $this->add('GET', $route, $function, $class);
  }
  public function post($route, $function, $class = false){
    $this->add('POST', $route, $function, $class);
  }
  public function put($route, $function, $class = false){
    $this->add('PUT', $route, $function, $class);
  }
  public function delete($route, $function, $class = false){
    $this->add('DELETE', $route, $function, $class);
  }
  public function patch($route, $function, $class = false){
    $this->add('PATCH', $route, $function, $class);
  }
  public function custom($type, $route, $function, $class = false){
    $this->add($type, $route, $function, $class);
  }

  public function before($function){
    $this->hooks['before_route'] = $function;
  }

  public function after($function){
    $this->hooks['after_route'] = $function;
  }

  public function run($container = false){
    if($container !== false) $this->container = $container;

    $rawUri = $_SERVER['REQUEST_URI'];

    $uri = $rawUri;
    if(($pos = strpos($uri, "?")) !== false) $uri = substr($uri, 0, $pos);

    $uri = rtrim($uri, '/');

    if($this->hooks['before_route'] !== false){
      call_user_func(\Closure::bind($this->hooks['before_route'], $this));
    }

    $found = false;
    foreach($this->routes as $route){
      if($_SERVER['REQUEST_METHOD'] !== $route['method']) continue;

      if($route['path'] === $rawUri && is_callable($route['function'])){
        $found = true;

        call_user_func(\Closure::bind($route['function'], $this));

        break;
      }

      $path = trim($route['path'], '/');

      $parts = explode('/', $path);

      $pattern = "/^";
      foreach ($parts as $part){
        if(strpos($part, '?}') !== false){
          $pattern .= preg_replace('/(\{)(.*?)(\})/', "(?>\/([A-z0-9\-\_\.]+))?", $part);
        }elseif (strpos($part, '}')){
          $pattern .= preg_replace('/(\{)(.*?)(\})/', "(?>\/([A-z0-9\-\_\.]+))", $part);
        }else{
          $pattern .= "\/$part";
        }
      }
      $pattern .= "$/";

      $match = preg_match($pattern, $uri, $values);

      if($match === 1 && is_callable($route['function'])){
        $found = true;

        unset($values[0]);

        call_user_func_array(\Closure::bind($route['function'], $this), $values);

        break;
      }
    }

    if($this->hooks['after_route'] !== false){
      call_user_func(\Closure::bind($this->hooks['after_route'], $this));
    }

    if(!$found){
      if(is_callable($this->notFound)){
        die(call_user_func($this->notFound));
      }
      die("<h2>404</h2>Sorry, but the page you are looking for is not there.");
    }
  }

  public function notFound($function){
    $this->notFound = $function;
  }
}
