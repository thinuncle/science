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

class UpdateModuleAction
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
        $id_module = $request->getAttribute('id_module');
        $id_user = $request->getAttribute('id_user');
        $parentUser = $this->userMapper->loadByUser($username);
        $data = $request->getParsedBody();

        $status = $data['status'];

        if (!in_array($status, [StructureMapper::STATUS_DONE, StructureMapper::STATUS_IN_PROGRESS])) {
            $problem = new ApiProblem(
                'Validation failed',
                'about:blank',
                400
            );
            $problem['errors'] = 'Niepoprawny status';

            throw new ProblemException($problem);
        }

        $this->structureMapper->updateModuleStatus($id_module, $id_user, $parentUser->getId(), $status);

        $module = $this->structureMapper->loadModuleIdByModuleId($id_module);



        $this->structureMapper->setNextStep($module['id_competition'], $id_user, $parentUser);

        $transformer = new BlockTransformer();
        $hal = $transformer->transformStatus('1');

        $response = $this->renderer->render($request, $response, $hal);
        return $response->withStatus(200);
    }
}
