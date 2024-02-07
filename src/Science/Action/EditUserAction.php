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

class EditAuthorAction
{
    protected $logger;
    protected $renderer;
    protected $authorMapper;

    public function __construct(Logger $logger, HalRenderer $renderer, AuthorMapper $authorMapper)
    {
        $this->logger = $logger;
        $this->renderer = $renderer;
        $this->authorMapper = $authorMapper;
    }

    public function __invoke($request, $response)
    {
        $id = $request->getAttribute('id');
        $data = $request->getParsedBody();
        $this->logger->info("Updating an author", ['id' => $id, 'data' => $data]);

        $author = $this->authorMapper->loadById($id);
        if (!$author) {
            $problem = new ApiProblem(
                'Could not find author',
                'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html',
                404
            );
            throw new ProblemException($problem);
        }

        $author->update($data);
        $this->authorMapper->update($author);

        $transformer = new AuthorTransformer();
        $hal = $transformer->transform($author);

        $response = $this->renderer->render($request, $response, $hal);
        return $response->withStatus(200);
    }
}
