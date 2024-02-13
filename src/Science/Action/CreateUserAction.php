<?php
namespace Science\Action;

use Science\User;
use Science\AuthorMapper;
use Science\AuthorTransformer;
use Monolog\Logger;
use RKA\ContentTypeRenderer\HalRenderer;
use Science\UserMapper;
use Science\UserTransformer;
use Error\ApiProblem;
use Error\Exception\ProblemException;

class CreateUserAction
{
    protected $logger;
    protected $renderer;
    protected $authorMapper;

    public function __construct(Logger $logger, HalRenderer $renderer, UserMapper $userMapper)
    {
        $this->logger = $logger;
        $this->renderer = $renderer;
        $this->userMapper = $userMapper;
    }

    public function __invoke($request, $response)
    {
        $data = $request->getParsedBody();
        $this->logger->info("Creating a new user", ['data' => $data]);

        if (isset($data['parent_email']) && !empty($data['parent_email'])) {
            $parent_user_id = $this->userMapper->loadByUser($data['parent_email']);

            if (!$parent_user_id) {
                $problem = new ApiProblem(
                    'Validation failed',
                    'about:blank',
                    400
                );
                $problem['errors'] = ['Nie istnieje nauczyciel o taki adresie e-mail'];
                throw new ProblemException($problem);
            } else {
                $data['parent_id'] = $parent_user_id->getId();
            }

        }

        $user_id = $this->userMapper->loadByUser($data['username']);
        if ($user_id) {
            $problem = new ApiProblem(
                'Validation failed',
                'about:blank',
                400
            );
            $problem['errors'] = ['Użytkownik o podanym adresie już istnieje'];
            throw new ProblemException($problem);
        }

        $user = new User($data);

        $this->userMapper->insert($user);

        $transformer = new UserTransformer();
        $hal = $transformer->transform($user);

        $response = $this->renderer->render($request, $response, $hal);
        return $response->withStatus(201);
    }
}
