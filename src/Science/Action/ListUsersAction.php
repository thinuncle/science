<?php
namespace Science\Action;

use Monolog\Logger;
use RKA\ContentTypeRenderer\HalRenderer;
use Science\StructureMapper;
use Science\UserMapper;
use Science\UserTransformer;

class ListUsersAction
{
    protected $logger;
    protected $renderer;
    protected $userMapper;
    protected $structureMapper;

    public function __construct(Logger $logger, HalRenderer $renderer, StructureMapper $structureMapper, UserMapper $userMapper)
    {
        $this->logger = $logger;
        $this->renderer = $renderer;
        $this->userMapper = $userMapper;
        $this->structureMapper = $structureMapper;
    }

    public function __invoke($request, $response)
    {
        $this->logger->info("Listing all users");

        $username = $request->getAttribute('username');
        $parentUser = $this->userMapper->loadByUser($username);

        $checklists = $this->structureMapper->fetchAllCompetitionsByParentId($parentUser->getId());

        $transformer = new UserTransformer();
        $hal = $transformer->transformCollection($checklists);


        return $this->renderer->render($request, $response, $hal);
    }
}
