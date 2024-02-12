<?php
// Routes

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
});

$app->get('/', App\Action\HomeAction::class);
$app->get('/ping', App\Action\PingAction::class);

// Routes that need a valid user token
$app->group('', function () use ($app) {
    // Authors
    $app->get('/users', Science\Action\ListUsersAction::class);
    $app->get('/faqs', Science\Action\ListFaqsAction::class);
    $app->get('/blocks/{id_user}', Science\Action\ListBlocksAction::class);
    $app->get('/blocks/{id}/{id_user}', Science\Action\ListCompetitionsAction::class);
    $app->get('/blocks/{id}/{id_competition}/{id_user}', Science\Action\ListActualModuleAction::class);
    $app->post('/blocks/{id}/{id_competition}/{id_user}', Science\Action\StartCompetitionAction::class);
    $app->post('/flows/{id_module}/{id_user}', Science\Action\UpdateModuleAction::class);
    $app->post('/tasks/{id_user}', Science\Action\AddTaskValuesAction::class);
    //$app->post('/users', Science\Action\CreateUserAction::class);
    //$app->get('/users/{id}', Science\Action\GetAuthorAction::sclass);
    $app->put('/users/{id}', Science\Action\EditAuthorAction::class);
    $app->delete('/authors/{id}', Science\Action\DeleteAuthorAction::class);

    $app->post('/authorise', Auth\Action\AuthoriseAction::class);
})->add(Auth\GuardMiddleware::class);

//add users
$app->post('/users', Science\Action\CreateUserAction::class);

// Auth routes
$app->post('/token', Auth\Action\TokenAction::class);
