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

class ListActualModuleAction
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
        $id_competition = $request->getAttribute('id_competition');
        $id_user = $request->getAttribute('id_user');
        $parentUser = $this->userMapper->loadByUser($username);
        $data = $request->getParsedBody();

        $user = $this->userMapper->loadById($id_user);



        $structureData = $this->structureMapper->getCompetitionForUser($id_competition, $id_user, $parentUser->getId());




        $transformer = new BlockTransformer();
        $hal = $transformer->transformCollecionModules($structureData, $user);

        return $this->renderer->render($request, $response, $hal);
    }
}
