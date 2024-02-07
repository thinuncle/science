<?php
namespace Science;

use Error\ApiProblem;
use Error\Exception\ProblemException;
use Zend\InputFilter\Factory as InputFilterFactory;

class Block
{
    protected $id;
    protected $name;
    protected $infographic;

    public function __construct(array $data)
    {

        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->infographic = $data['infographic'] ?? null;


    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'infographic' => $this->infographic
        ];
    }


    public function getId()
    {
        return $this->id;
    }
}
