<?php
/**
 * Created by PhpStorm.
 * User: Dell
 * Date: 01.02.24
 * Time: 15:00
 */

namespace Science\Action;

use Monolog\Logger;
use RKA\ContentTypeRenderer\HalRenderer;
use Science\StructureMapper;
use Science\BlockTransformer;


class ListFaqsAction {
    protected $logger;
    protected $renderer;
    protected $structureMapper;

    public function __construct(Logger $logger, HalRenderer $renderer, StructureMapper $structureMapper)
    {
        $this->logger = $logger;
        $this->renderer = $renderer;
        $this->structureMapper = $structureMapper;
    }

    public function __invoke($request, $response)
    {
        $this->logger->info("Listing faqs");


        $value = $this->structureMapper->getFaqs();

        $transformer = new BlockTransformer();
        $hal = $transformer->transformValue($value);

        $response = $this->renderer->render($request, $response, $hal);
        return $response->withStatus(200);
    }
} 