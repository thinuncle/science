<?php
// DIC configuration
$container[Science\UserMapper::class] = function ($c) {
    return new Science\UserMapper($c->get('logger'), $c->get('db'));
};

// Register AuthServer services
$container->register(new Auth\OAuth2ServerProvider());

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    if (!empty($settings['path'])) {
        $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    } else {
        $logger->pushHandler(new Monolog\Handler\ErrorLogHandler(0, Monolog\Logger::DEBUG, true, true));
    }
    return $logger;
};

// HAL renderer
$container['renderer'] = function ($c) {
    return new RKA\ContentTypeRenderer\HalRenderer();
};

// Database adapter
$container['db'] = function ($c) {
    $db = $c->get('settings')['db'];

    $pdo = new PDO($db['dsn'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    if (strpos($db['dsn'], 'sqlite') === 0) {
        $pdo->exec('PRAGMA foreign_keys = ON');
    }

    return $pdo;
};

// Error handlers
$container['notFoundHandler'] = function () {
    return new Error\Handler\NotFound();
};
$container['notAllowedHandler'] = function () {
    return new Error\Handler\NotAllowed();
};
$container['errorHandler'] = function () {
    return new Error\Handler\Error();
};
$container['phpErrorHandler'] = function () {
    return new Error\Handler\Error();
};

// Mappers

$container[Science\StructureMapper::class] = function ($c) {
    return new Science\StructureMapper($c->get('logger'), $c->get('db'));
};

// Actions
$container[App\Action\HomeAction::class] = function ($c) {
    $logger = $c->get('logger');
    $renderer = $c->get('renderer');
    return new App\Action\HomeAction($logger, $renderer);
};

$container[App\Action\PingAction::class] = function ($c) {
    $logger = $c->get('logger');
    return new App\Action\PingAction($logger);
};

$defaultActionFactory= function ($actionClass) {
    return function ($c) use ($actionClass) {
        $logger = $c->get('logger');
        $renderer = $c->get('renderer');
        $mapper = $c->get(Science\UserMapper::class);
        return new $actionClass($logger, $renderer, $mapper);
    };
};

$defaultStructureActionFactory= function ($actionClass) {
    return function ($c) use ($actionClass) {
        $logger = $c->get('logger');
        $renderer = $c->get('renderer');
        $mapper = $c->get(Science\StructureMapper::class);
        return new $actionClass($logger, $renderer, $mapper);
    };
};

$defaultUserStructureActionFactory= function ($actionClass) {
    return function ($c) use ($actionClass) {
        $logger = $c->get('logger');
        $renderer = $c->get('renderer');
        $mapper = $c->get(Science\StructureMapper::class);
        $user_mapper = $c->get(Science\UserMapper::class);
        return new $actionClass($logger, $renderer, $mapper, $user_mapper);
    };
};

$defaultUserStructureActionSettingsFactory= function ($actionClass) {
    return function ($c) use ($actionClass) {
        $logger = $c->get('logger');
        $renderer = $c->get('renderer');
        $mapper = $c->get(Science\StructureMapper::class);
        $user_mapper = $c->get(Science\UserMapper::class);
        $settings = $c->get('settings')['settings'];
        return new $actionClass($logger, $renderer, $mapper, $user_mapper, $settings);
    };
};

// @codingStandardsIgnoreStart
$container[Science\Action\ListUsersAction::class] = $defaultUserStructureActionFactory(Science\Action\ListUsersAction::class);
//$container[Science\Action\GetAuthorAction::class] = $defaultActionFactory(Science\Action\GetAuthorAction::class);
$container[Science\Action\CreateUserAction::class] = $defaultActionFactory(Science\Action\CreateUserAction::class);
$container[Science\Action\ListBlocksAction::class] = $defaultUserStructureActionFactory(Science\Action\ListBlocksAction::class);
$container[Science\Action\ListCompetitionsAction::class] = $defaultUserStructureActionFactory(Science\Action\ListCompetitionsAction::class);
$container[Science\Action\AddTaskValuesAction::class] = $defaultUserStructureActionSettingsFactory(Science\Action\AddTaskValuesAction::class);
$container[Science\Action\UpdateModuleAction::class] = $defaultUserStructureActionFactory(Science\Action\UpdateModuleAction::class);
$container[Science\Action\StartCompetitionAction::class] = $defaultUserStructureActionFactory(Science\Action\StartCompetitionAction::class);
$container[Science\Action\ListActualModuleAction::class] = $defaultUserStructureActionFactory(Science\Action\ListActualModuleAction::class);
$container[Science\Action\ListFaqsAction::class] = $defaultStructureActionFactory(Science\Action\ListFaqsAction::class);

//$container[Science\Action\EditAuthorAction::class] = $defaultActionFactory(Science\Action\EditAuthorAction::class);
//$container[Science\Action\DeleteAuthorAction::class] = $defaultActionFactory(Science\Action\DeleteAuthorAction::class);
// @codingStandardsIgnoreEnd
