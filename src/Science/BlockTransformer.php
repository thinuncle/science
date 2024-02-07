<?php
namespace Science;

use Nocarrier\Hal;

/**
 * Transform an Author (or collection of Authors) into Hal resource
 */
class BlockTransformer
{
    public function transformCollectionBlocks($blocks, $user)
    {
        $hal = new Hal('/blocks');
        $userTransformer = new UserTransformer();

        $count = 0;
        foreach ($blocks as $block) {
            $count++;
            $hal->addResource('blocks', $this->transformBlock($block));
        }

        $hal->setData(['count' => $count,'user' =>$user->getArrayCopy()]);

        return $hal;
    }

    public function transformBlock($user)
    {
        $data = $user->getArrayCopy();

        $resource = new Hal('/blocks/' . $data['id'], $data);
       // $resource->addLink('books', '/authors/' . $data['author_id'] . '/books');

        return $resource;
    }

    public function transformCollectionCompetitions($competitions, $user)
    {
        $hal = new Hal('/competitions');

        $userTransformer = new UserTransformer();

        $count = 0;
        foreach ($competitions as $competition) {
            $count++;
            $hal->addResource('competitions', $this->transformCompetition($competition));
        }

        $block_id = '';
        $block_name = '';

        if (!empty($competitions)) {
            $block_id = $competitions[0]->getBlockId();
            $block_name = $competitions[0]->getBlockName();
        }


        $hal->setData(['count' => $count,'user' =>$user->getArrayCopy(), 'block_id' => $block_id, 'block_name' => $block_name]);

        return $hal;
    }

    public function transformCompetition($competition)
    {
        $data = $competition->getArrayCopy();

        $resource = new Hal('/blocks/' . $data['parent_id'] . '/' . $data['id'], $data);
        // $resource->addLink('books', '/authors/' . $data['author_id'] . '/books');

        return $resource;
    }

    public function transformStatus($value)
    {
        $status['status'] = $value;
        $resource = new Hal(null, $status);
        // $resource->addLink('books', '/authors/' . $data['author_id'] . '/books');

        return $resource;
    }
    public function transformValue($value)
    {
        $status['value'] = $value;
        $resource = new Hal(null, $status);
        // $resource->addLink('books', '/authors/' . $data['author_id'] . '/books');

        return $resource;
    }

    public function transformCollecionModules($modules, $user)
    {
        $hal = new Hal('/steps');

        $count = 0;
        foreach ($modules as $module) {
            $count++;
            $hal->addResource('steps',  new Hal(null, $module));
        }

        $block_id = '';
        $block_name = '';
        $competition_id = '';
        $competition_name = '';

        if (!empty($modules)) {
            $block_id = $modules[0]['block_id'];
            $block_name = $modules[0]['block_name'];
            $competition_id = $modules[0]['competition_id'];
            $competition_name = $modules[0]['competition_name'];
        }

        $hal->setData(['count' => $count, 'user' => $user->getArrayCopy() ,'block_id' => $block_id, 'block_name' => $block_name, 'competition_id' => $competition_id, 'competition_name' => $competition_name]);

        return $hal;
    }
}
