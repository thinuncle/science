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

class ListBlocksAction
{
    protected $logger;
    protected $renderer;
    protected $structureMapper;
    protected $userMapper;

    public function __construct(Logger $logger, HalRenderer $renderer, StructureMapper $structureMapper,  UserMapper $userMapper)
    {
        $this->logger = $logger;
        $this->renderer = $renderer;
        $this->structureMapper = $structureMapper;
        $this->userMapper = $userMapper;
    }

    public function __invoke($request, $response)
    {
        $this->logger->info("Listing all blocks");

        $id_user = $request->getAttribute('id_user');

        $username = $request->getAttribute('username');
        $parentUser = $this->userMapper->loadByUser($username);
        $user = $this->userMapper->loadById($id_user);

        $url = $request->getUri()->getBaseUrl().'/image/';

        $blocks = $this->structureMapper->fetchAllBlocks($id_user, $parentUser, $url);


        $transformer = new BlockTransformer();
        $hal = $transformer->transformCollectionBlocks($blocks, $user);


        return $this->renderer->render($request, $response, $hal);
    }
}
