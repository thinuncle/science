<?php
namespace Science;

use Nocarrier\Hal;

/**
 * Transform an Author (or collection of Authors) into Hal resource
 */
class UserTransformer
{
    public function transformCollection($users)
    {
        $hal = new Hal('/users');

        $count = 0;
        foreach ($users as $user) {
            $count++;
            $hal->addResource('users', $this->transform($user));
        }

        $hal->setData(['count' => $count]);

        return $hal;
    }

    public function transform($user)
    {
        $data = $user->getArrayCopy();


        $resource = new Hal('/users/' . $data['username'], $data);
       // $resource->addLink('books', '/authors/' . $data['author_id'] . '/books');

        return $resource;
    }
}
