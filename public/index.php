<?php


// Подключение автозагрузки через composer
require __DIR__.'/../vendor/autoload.php';

use Slim\Factory\AppFactory;
use DI\Container;


$container = new Container();
$container->set('renderer', function () {
    // Параметром передается базовая директория, в которой будут храниться шаблоны
    return new \Slim\Views\PhpRenderer(__DIR__.'/../templates');
});
$app = AppFactory::createFromContainer($container);
$app->addErrorMiddleware(true, true, true);

$app->get('/', function ($request, $response) {
    $response->getBody()->write('Welcome to Slim!');

    return $response;
    // Благодаря пакету slim/http этот же код можно записать короче
    // return $response->write('Welcome to Slim!');
});

// $app->get('/users', function ($request, $response) {
//     return $response->write('GET /users');
// });

// $app->post('/users', function ($request, $response) {
//     return $response->withStatus(302);
// });

$app->get('/courses/{id}', function ($request, $response, array $args) {
    $id = $args['id'];

    return $response->write("Course id: {$id}");
});

$app->get('/users/{id:[0-9]+}', function ($request, $response, $args) {
    $params = ['id' => $args['id'], 'nickname' => 'user-'.$args['id']];
    // Указанный путь считается относительно базовой директории для шаблонов, заданной на этапе конфигурации
    // $this доступен внутри анонимной функции благодаря https://php.net/manual/ru/closure.bindto.php
    // $this в Slim это контейнер зависимостей
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
});


$app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['nickname' => '', 'email' => ''],
    ];

    return $this->get('renderer')->render($response, "users/new.phtml", $params);
});


$app->post('/users', function ($request, $response) {
    $user = $request->getParsedBodyParam('user');

    $file = __DIR__.'/../data/users.json';

    $file_json = file_get_contents($file);      //Читает содержимое файла в строку
    $array     = json_decode($file_json, true); //Преобразуем JSON в массив PHP


    $maxUserId = 1;
    foreach ($array as $userId => $userData) {
        if ($userData['id'] > $maxUserId) {
            $maxUserId = $userData['id'];
        }
    }
    $user['id'] = $maxUserId + 1; //генерируемое id

    $array [$user['id']] = $user; // добавляем в массив пользователя


    $jsonData = json_encode($array, JSON_PRETTY_PRINT);
    file_put_contents($file, $jsonData);


    $params = [
        'user' => $user,
    ];


    return $this->get('renderer')->render($response, "users/new.phtml", $params);
});
$app->run();