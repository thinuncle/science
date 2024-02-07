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

class AddTaskValuesAction
{
    protected $logger;
    protected $renderer;
    protected $structureMapper;
    protected $userMapper;
    protected $settings;

    public function __construct(Logger $logger, HalRenderer $renderer, StructureMapper $structureMapper, UserMapper $userMapper, array $settings)
    {
        $this->logger = $logger;
        $this->renderer = $renderer;
        $this->structureMapper = $structureMapper;
        $this->userMapper = $userMapper;
        $this->settings = $settings;
    }

    public function __invoke($request, $response)
    {
        $username = $request->getAttribute('username');
        $assigned_user_id = $request->getAttribute('id_user');
        $parentUser = $this->userMapper->loadByUser($username);
        $data = $request->getParsedBody();

        $files = $request->getUploadedFiles();


       /* print_r($data);
        die();*/
        /*$url = $request->getUri()->getBaseUrl();

        print_r($uri);*/

        foreach($files as $key => $file) {
            foreach ($file as $fileObject) {
                if ($fileObject->getError() === UPLOAD_ERR_OK) {
                    $fileObject->moveTo($this->settings['upload'].$fileObject->getClientFilename());
                    $url = $request->getUri()->getBaseUrl();
                    $data[$key]['files'][] = $url.'/upload/'.$fileObject->getClientFilename();


                } else {
                    $problem = new ApiProblem(
                        'Wystąpil problem z uploadem pliku: '.$fileObject->getClientFilename(),
                        'about:blank',
                        500
                    );
                    throw new ProblemException($problem);
                }
            }
        }




        $this->structureMapper->updateTaskValues($data, $parentUser->getId(), $assigned_user_id);

        $transformer = new BlockTransformer();
        $hal = $transformer->transformStatus('1');

        $response = $this->renderer->render($request, $response, $hal);
        return $response->withStatus(200);
    }
}
