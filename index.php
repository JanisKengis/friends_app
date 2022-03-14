<?php
session_start();
require_once 'vendor/autoload.php';

use App\Controllers\ArticleController;
use App\Controllers\LogoutController;
use App\Controllers\UsersController;
use App\Controllers\HomeController;
use App\Controllers\ArticleCommentsController;
use App\Redirect;
use App\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;



$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    // Home
    $r->addRoute('GET', '/', [HomeController::class, 'home']);

    //Users
    $r->addRoute('GET', '/users', [UsersController::class, 'index']);
    $r->addRoute('GET', '/users/{id:\d+}', [UsersController::class, 'show']);

    $r->addRoute('GET', '/users/signup', [UsersController::class, 'signup']);
    $r->addRoute('POST', '/users', [UsersController::class, 'register']);

    $r->addRoute('POST', '/users/{id:\d+}/invite', [UsersController::class, 'invite']);
    $r->addRoute('POST', '/users/{id:\d+}/accept', [UsersController::class, 'accept']);
    $r->addRoute('POST', '/users/{id:\d+}/decline', [UsersController::class, 'decline']);

    $r->addRoute('GET', '/users/login', [UsersController::class, 'login']);
    $r->addRoute('POST', '/users/login', [UsersController::class, 'signin']);
    $r->addRoute('GET', '/users/error', [UsersController::class, 'error']);

    $r->addRoute('POST', '/logout', [LogoutController::class, 'logout']);

    //Articles
    $r->addRoute('GET', '/articles', [ArticleController::class, 'index']);
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticleController::class, 'show']);

    $r->addRoute('POST', '/articles', [ArticleController::class, 'store']);
    $r->addRoute('GET', '/articles/create', [ArticleController::class, 'create']);

    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticleController::class, 'delete']);

    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticleController::class, 'edit']);
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticleController::class, 'update']);

    $r->addRoute('POST', '/articles/{id:\d+}/like', [ArticleController::class, 'like']);
    $r->addRoute('POST', '/articles/{id:\d+}/dislike', [ArticleController::class, 'dislike']);

    // Comments
    $r->addRoute('POST', '/articles/{articleId:\d+}/comment', [ArticleCommentsController::class, 'storeComment']);
    $r->addRoute('GET', '/articles/{articleId:\d+}/comment', [ArticleCommentsController::class, 'createComment']);
    $r->addRoute('POST', '/articles/{articleId:\d+}/comment/{id:\d+}/deletecomment', [ArticleCommentsController::class, 'deleteComment']);
});


// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        var_dump('404 Not Found');
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        var_dump('405 Method Not Allowed');
        break;
    case FastRoute\Dispatcher::FOUND:
        $controller =  $routeInfo[1][0];
        $method = $routeInfo[1][1];

        /** @var View $response */
        $response = (new $controller)->$method($routeInfo[2]);
        $twig = new Environment(new FilesystemLoader('app/Views'));

        if ($response instanceof View) {
            echo $twig->render($response->getPath() . '.html', $response->getData());
        }

        if ($response instanceof Redirect) {
            header('Location:'.$response->getLocation());
            exit;
        }
        break;
}

if (isset($_SESSION['errors'])) {
    unset($_SESSION['errors']);
}

if (isset($_SESSION['inputs'])) {
    unset($_SESSION['inputs']);
}