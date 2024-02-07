<?php
namespace Science\Action;

use Science\Author;
use Science\AuthorMapper;
use Science\AuthorTransformer;
use Error\ApiProblem;
use Error\Exception\ProblemException;
use Monolog\Logger;
use RKA\ContentTypeRenderer\ApiProblemRenderer;
use RKA\ContentTypeRenderer\HalRenderer;
use Science\BlockTransformer;
use Science\StructureMapper;
use Science\UserMapper;

class StartCompetitionAction
{
    protected $logger;
    protected $renderer;
    protected $structureMapper;
    protected $userMapper;

    public function __construct(Logger $logger, HalRenderer $renderer, StructureMapper $structureMapper, UserMapper $userMapper)
    {
        $this->logger = $logger;
        $this->renderer = $renderer;
        $this->structureMapper = $structureMapper;
        $this->userMapper = $userMapper;
    }

    public function __invoke($request, $response)
    {
        $username = $request->getAttribute('username');
        $competition_id = $request->getAttribute('id_competition');
        $assigned_user_id = $request->getAttribute('id_user');

        $parentUser = $this->userMapper->loadByUser($username);
        $data = $request->getParsedBody();



        $this->structureMapper->startCompetition($competition_id, $assigned_user_id, $parentUser->getId() );

        $transformer = new BlockTransformer();
        $hal = $transformer->transformStatus('1');

        $response = $this->renderer->render($request, $response, $hal);
        return $response->withStatus(200);
    }
}
