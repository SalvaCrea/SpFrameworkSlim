<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

namespace Chocolatine\Services;

use Medoo\Medoo;

class Router extends \Chocolatine\Pattern\Service{

  public $name = 'Router';

  /**
   * Requete http
   * @var array
   */
  public $requete;
  /**
   * Response Http
   * @var array
   */
  public $response;
  /**
   * Argument in url or post
   * @var array
   */
  public $args;
  /**
   * List routes
   * @var array
   */
  public $routes = array();
  /**
   * Current patern
   * @var string
   */
  public $current_pattern;
  /**
   * The route Current
   * @var array
   */
  public $current_root;
  public function __construct(){

      $config = ['settings' => [
          'addContentLengthHeader' => false,
          'displayErrorDetails' => true
      ]];

      $this->router =  new \Slim\App( $config );
  }
  // use for declarate all routes
  public function declare_routes(){

        $this->routes = \Chocolatine\get_configuration( 'routes' );
        $view_manager = \Chocolatine\get_manager('view');

        /**
         *  Declare all route
         */
        foreach ( $this->routes as $key => $current_route) {

            $this->router->map(['GET', 'POST'],  $current_route['route'] ,function ($request, $response, $args) {

                $router = \Chocolatine\get_service( 'Router' );
                return $router->controller( $request, $response, $args );

            });

        }

  }
  public function use_routes(){
        $this->router->run();
  }
  /**
   * Call the view linked with the root
   * @param  object $request  Psr7 Http Request
   * @param  object $response Psr7 Http Response
   * @param  array  $arg      $argument post or get or ....
   * @return object           Psr7 Http Response
   */
  public function controller( $request, $response, $arg = [] ){

        $route = $request->getAttribute('route');
        $this->current_pattern = $route->getPattern();

        $this->find_current_route();

        $uri = $request->getUri();
        $path = $uri->getPath();

        $this->request = $request;
        $this->response = $response;
        $this->arg = $arg;

        $this->use_view();
        /**
         * Execute the motor of template
         */
        \Chocolatine\get_service( 'templator' )->renderer();
  }
  public function use_view()
  {
    $view_manager = \Chocolatine\get_manager('view');

    if ( false !== $view = $view_manager->find( $this->current_route['view'] ) ) {

        $view = $view->make();

        if ( isset (  $this->current_route['method'] ) ) {
          $method = $this->current_route['method'];
        }else{
          $method = 'main';
        }

        $view->{$method}();
    }
  }
  /**
   * Return and Find the current root
   * @return array the current_route
   */
  public function find_current_route()
  {

        if ( false !== $key = \Chocolatine\array_find( $this->routes, 'route', $this->current_pattern ) ) {
          $this->current_route = $this->routes[ $key ];
        }

        return $this->current_route;
  }
}
