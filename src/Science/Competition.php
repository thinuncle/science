<?php
namespace Science;


class Competition
{
    protected $id;
    protected $name;
    protected $parent_id;
    protected $number;
    protected $status;
    protected $infographic;
    protected  $block_id;
    protected  $block_name;

    public function __construct(array $data)
    {

        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->number = $data['number'] ?? null;
        $this->status = $data['status'] ?? null;
        $this->infographic = $data['infographic'] ?? null;
        $this->block_id = $data['block_id'] ?? null;
        $this->block_name = $data['block_name'] ?? null;
    }

    public function getArrayCopy()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parent_id' => $this->parent_id,
            'number' => $this->number,
            'status' => $this->status,
            'infographic' => $this->infographic,
            'block_id' => $this->block_id,
            'block_name' => $this->block_name
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBlockId()
    {
        return $this->block_id;
    }
    public function getBlockName()
    {
        return $this->block_name;
    }
}
