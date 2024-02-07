<?php
namespace Auth\Action;

use OAuth2;
use Science\UserMapper;

class TokenAction
{
    protected $server;
    protected $userMapper;

    public function __construct($server, UserMapper $userMapper)
    {
        $this->server = $server;
        $this->userMapper = $userMapper;
    }

    public function __invoke($request, $response)
    {
        $serverRequest = OAuth2\Request::createFromGlobals();
        $serverResponse = $this->server->handleTokenRequest($serverRequest);
        //var_dump($this->server->getGrantType('password')->getUserId());
        $user = $this->userMapper->loadByUser($this->server->getGrantType('password')->getUserId());

        $serverResponse->setParameter('user_id',$user->getId());
        $serverResponse->setParameter('role', $user->getRoleId() == 1 ? UserMapper::ROLE_PRINCIPLE : UserMapper::ROLE_GUARDIAN);
        $serverResponse->setParameter('subRole',$user->getSubRole());
        //var_dump($serverResponse->getParameters());
        //die();


        $serverResponse->send();
        exit;

        // If we wanted to conver to a PSR-7 response, it would look something like this:
        /*
        $response = $response->withStatus($serverResponse->getStatusCode());
        foreach ($serverResponse->getHttpHeaders() as $name => $value) {
            $response = $response->withHeader($name, $value);
        }
        $response = $response->withHeader('Content-Type', 'application/json');
        return $response->write($serverResponse->getResponseBody('json'));
        */
    }
}
