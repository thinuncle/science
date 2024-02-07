<?php
namespace Science\Action;

use Science\AuthorMapper;
use Science\AuthorTransformer;
use Monolog\Logger;
use RKA\ContentTypeRenderer\HalRenderer;
use Science\BlockTransformer;
use Science\StructureMapper;
use Science\UserMapper;
use Science\UserTransformer;

class ListCompetitionsAction
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
        $this->logger->info("Get Block");

        $id = $request->getAttribute('id');
        $id_user = $request->getAttribute('id_user');

        $username = $request->getAttribute('username');
        $parentUser = $this->userMapper->loadByUser($username);
        $user = $this->userMapper->loadById($id_user);

        $blocks = $this->structureMapper->fetchCompetitionsByBlock($id, $id_user, $parentUser->getId(), $parentUser->getRoleId());

        $transformer = new BlockTransformer();
        $hal = $transformer->transformCollectionCompetitions($blocks, $user);


        return $this->renderer->render($request, $response, $hal);
    }
}
