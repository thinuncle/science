<?php
namespace Science;

use Error\ApiProblem;
use Error\Exception\ProblemException;
use Zend\InputFilter\Factory as InputFilterFactory;

class User
{
    protected $id;
    protected $username;
    protected $password;
    protected $role_id;
    protected $parent_id;
    protected $block1;
    protected $block2;
    protected $block3;
    protected $firstname;
    protected $lastname;
    protected $role_type;

    public function __construct(array $data)
    {
        //$data = $this->validate($data);



        $this->id = $data['id'] ?? null;
        $this->username = $data['username'] ?? null;
        $this->password = $data['password'] ?? null;
        $this->role_id = $data['role_id'] ?? null;
        $this->parent_id = $data['parent_id'] ?? null;
        $this->block1 = $data['block1'] ?? null;
        $this->block2 = $data['block2'] ?? null;
        $this->block3 = $data['block3'] ?? null;
        $this->firstname = $data['firstName'] ?? null;
        $this->lastname = $data['lastName'] ?? null;
        $this->role_type = $data['subRole'] ?? null;


    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
            'role_id' => $this->role_id,
            'parent_id' => $this->parent_id,
            'block1' => $this->block1,
            'block2' => $this->block2,
            'block3' => $this->block3,
            'firstName' => $this->firstname,
            'lastName' => $this->lastname,
            'subRole' => $this->role_type
        ];
    }

    public function update($data)
    {
        /*$data = $this->validate($data, ['name', 'biography', 'date_of_birth']);

        $this->name = $data['name'] ?? $this->name;
        $this->biography = $data['biography'] ?? $this->biography;
        $this->date_of_birth = $data['date_of_birth'] ?? $this->date_of_birth;*/
    }

    /**
     * Validate data to be applied to this entity
     *
     * @param  array $data
     * @return array
     */
    public function validate($data, $elements = [])
    {
        $inputFilter = $this->createInputFilter($elements);
        $inputFilter->setData($data);

        if ($inputFilter->isValid()) {
            return $inputFilter->getValues();
        }

        $problem = new ApiProblem(
            'Validation failed',
            'about:blank',
            400
        );
        $problem['errors'] = $inputFilter->getMessages();

        throw new ProblemException($problem);
    }

    protected function createInputFilter($elements = [])
    {
        $specification = [
            'id' => [
                'required' => false,
            ],
            'username' => [
                'required' => true,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ],
            'password' => [
                'required' => false,
                'filters' => [
                    ['name' => 'StringTrim'],
                    ['name' => 'StripTags'],
                ],
            ],
            'parent_id' => [
                'required' => false,
            ],
            'role_id' => [
                'required' => false,
            ],
        ];

        if ($elements) {
            $specification = array_filter(
                $specification,
                function ($key) use ($elements) {
                    return in_array($key, $elements);
                },
                ARRAY_FILTER_USE_KEY
            );
        }

        $factory = new InputFilterFactory();
        $inputFilter = $factory->createInputFilter($specification);

        return $inputFilter;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRoleId()
    {
        return $this->role_id;
    }
    public function getSubRole()
    {
        return $this->role_type;
    }
}
