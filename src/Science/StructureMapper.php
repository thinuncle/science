<?php
namespace Science;

use Monolog\Logger;
use Science\Block;
use Science\Tools;

class StructureMapper
{
    protected $logger;
    protected $db;

    CONST STATUS_NEW = 'new';
    CONST STATUS_IN_PROGRESS = 'in_progress';
    CONST STATUS_DONE = 'done';
    CONST ROLE_CHILD = '1';
    CONST ROLE_TEACHER = '2';
    CONST FIELD_NAME_INPUT_FILE = 'BaseFileInput';
    CONST PHOTO_EXT = 'jpg';

    public function __construct(Logger $logger, \PDO $db)
    {
        $this->logger = $logger;
        $this->db = $db;
    }

    /**
     * Fetch all authors
     *
     * @return [Author]
     */
    public function fetchAllBlocks($assigned_user_id, $parentUser, $url)
    {
        $sql = "SELECT id,name FROM blocks ORDER BY sort ASC";
        $stmt = $this->db->query($sql);

        $results = [];
        while ($row = $stmt->fetch()) {
            $infographic_file = $this->getInfographicForBlock($row['id'], $assigned_user_id);

            if (!empty($infographic_file) && file_exists( __DIR__ . '/../../public/image/' . $infographic_file)) {
                $row['infographic'] = $url.$infographic_file;
            } else {
                $row['infographic'] = '';
            }

            $results[] = new Block($row);
        }

        return $results;
    }

    public function fetchCompetitionsByBlock($id, $assigned_user_id, $user_id,$id_role)
    {
        $sql = "SELECT c.id,c.name,parent_id,number, infographic as infographic_block, b.id as block_id, b.name as block_name FROM competitions c
                INNER JOIN blocks b ON b.id = c.parent_id
                WHERE parent_id = $id ORDER BY c.sort ASC";
        $stmt = $this->db->query($sql);

        $results = [];
        while ($row = $stmt->fetch()) {
            $status = $this->checkStatus($row['id'], $assigned_user_id);

            if ($id_role == self::ROLE_CHILD) {
                $row['status'] = $status;
                $row['infographic'] = $this->getInfographicForUser($row['id'], $assigned_user_id, $user_id, $status);
                $results[] = new Competition($row);
                //break;
            }

            if ($id_role == self::ROLE_TEACHER) {
                $row['status'] = $status;
                $row['infographic'] = $this->getInfographicForUser($row['id'], $assigned_user_id, $user_id, $status);
                $results[] = new Competition($row);
                //break;
            }

        }

        return $results;
    }

    public function getInfographicForUser($id_competition, $id_assigned_user, $id_user, $status) {
        $sql = "SELECT infographic,IFNULL(mfu.status,'new') as status FROM modules m
               LEFT JOIN module_flows_users mfu ON (mfu.id_module = m.id AND mfu.assigned_user_id = $id_assigned_user AND mfu.modified_user_id = $id_user )
               WHERE m.id_competition = $id_competition";

        $stmt = $this->db->query($sql);

        $infographic = '';


        if($status == self::STATUS_IN_PROGRESS || $status == self::STATUS_NEW){
            while ($row = $stmt->fetch()) {
                if ($row['status'] == $status) {
                    return $row['infographic'];
                }
            }
        }



        return $infographic;
    }

    public function getInfographicForBlock($id, $assigned_user_id, $url = '') {
        $sql = "SELECT c.id FROM competitions c
                INNER JOIN blocks b ON b.id = c.parent_id
                WHERE parent_id = $id ORDER BY c.sort ASC";
        $stmt = $this->db->query($sql);

        $photo = '';

        while ($row = $stmt->fetch()) {
            $status = $this->checkStatus($row['id'], $assigned_user_id);

            if ($status == self::STATUS_IN_PROGRESS) {
                return $this->getActualPhoto($row['id'], $assigned_user_id);
            }


        }

        return $photo;
    }

    public function getActualPhoto($id, $assigned_user_id){
        $sql = "SELECT IFNULL(mfu.status,'new') as status, mfu.modified_user_id ,m.sequence, c.parent_id as block_id FROM modules m
                INNER JOIN competitions c ON c.id = m.id_competition
                LEFT JOIN module_flows_users mfu ON (mfu.id_module = m.id AND mfu.assigned_user_id = $assigned_user_id )
                WHERE m.id_competition = $id ORDER BY m.sequence ASC";

        $stmt = $this->db->query($sql);

        $tab_users['assigned_user_id'] = '';
        $tab_users['modified_user_id'] = '';

        while ($row = $stmt->fetch()) {
           $tab_users['block_id'] = $row['block_id'];
            if ($row['status'] == self::STATUS_NEW || $row['status'] == self::STATUS_IN_PROGRESS) {
               if ($row['modified_user_id'] == $assigned_user_id) {
                   if (empty($tab_users['assigned_user_id'])) {
                       $tab_users['assigned_user_id'] = $row['sequence'];
                   }
               } else {
                   if (empty($tab_users['modified_user_id'])) {
                       $tab_users['modified_user_id'] = $row['sequence'];
                   }
               }
           }
        }

        return $tab_users['block_id'] . '_' . $tab_users['assigned_user_id'] . '_' . $tab_users['modified_user_id'] . '.' . self::PHOTO_EXT;
    }


    public function getCompetitionForUser($id_competition, $assigned_user_id, $modified_user_id)
    {
       //$status = $this->checkStatus($id_competition, $assigned_user_id);

       $sql = "SELECT mfu.id_module as moduleId, m.name, r.name as role ,m.type, m.info_content as infoContent,mfu.status, c.id as competition_id,c.name as competition_name, b.id as block_id, b.name as block_name FROM module_flows_users mfu
               INNER JOIN modules m ON m.id = mfu.id_module
               INNER JOIN competitions c ON c.id = m.id_competition
               INNER JOIN blocks b ON b.id = c.parent_id
               INNER JOIN roles r ON r.id = m.id_role
               WHERE assigned_user_id = $assigned_user_id AND modified_user_id = $modified_user_id AND m.id_competition = $id_competition ORDER BY m.sequence ASC";

       $stmt = $this->db->query($sql);
       $results =  [];
       $isCurrentStep = 0;
       while ($row = $stmt->fetch()) {
           if (!$isCurrentStep && $row['status'] == self::STATUS_IN_PROGRESS  ) {
               $row['isCurrentStep']= $isCurrentStep = 1;
           } else {
               $row['isCurrentStep'] = 0;
           }
           $row['moduleId'] = $this->prepareFields($row['moduleId']);
           $row['taskDefinition'] = $this->getTasksForModule($row['moduleId']);
           $row['taskValues'] = $this->getTasksValuesForModule($row['moduleId'], $modified_user_id, $assigned_user_id);
           $results[] = $row;
       }

       if (empty($results)) {
           return [

           ];
       }

        return $results;
    }

    public function getTasksForModule($id)
    {
        $sql = "SELECT t.id, `type`, t.name, validationRules, r.name as role, defaultValue, requireAllFieldToNextStep FROM tasks t
                INNER JOIN roles r on r.id = t.id_role
                WHERE id_module = $id ORDER BY sort ASC";

        $stmt = $this->db->query($sql);
        $results =  [];

        while ($row = $stmt->fetch()) {
            $row['defaultValue'] = $this->prepareFields($row['defaultValue']);
            $row['requireAllFieldToNextStep'] = $this->prepareFields($row['requireAllFieldToNextStep']);
            $row['componentProps'] = $this->getComponentPropsForTask($row['id']);
            $results[] = $row;
        }

        return $results;
    }

    public function getTasksValuesForModule($id, $user_id, $assigned_user_id)
    {
        $sql = "SELECT t.name,ta.value FROM task_answers ta
                INNER JOIN tasks t ON t.id = ta.id_task
                WHERE t.id_module = $id AND ta.assigned_user_id = $assigned_user_id";

        $stmt = $this->db->query($sql);
        $results =  [];

        while ($row = $stmt->fetch()) {

            $results[$row['name']] = $this->prepareFields($row['value']);
        }

        return $results;
    }

    public function getComponentPropsForTask($id) {
        $sql = "SELECT id, name FROM task_properties WHERE id_task = $id";

        $stmt = $this->db->query($sql);
        $results =  [];

        while ($row = $stmt->fetch()) {

            $results[$row['name']] = $this->getOptionsForTask($row['id']);
        }

        return $results;

    }

    public function getOptionsForTask($id) {
        $sql = "SELECT id, value FROM property_values WHERE id_property = $id";

        $stmt = $this->db->query($sql);
        $results =  [];

        while ($row = $stmt->fetch()) {

            $results[] = $this->prepareFields($row['value']);
        }

        if (count($results) == 1) {
            $results = $results[0];
        }

        return (!empty($results)) ? $results : null;

    }

    public function checkStatus($id_competition, $id_user)
    {
        $sql = "SELECT status FROM modules m
               LEFT JOIN module_flows_users mfu ON mfu.id_module = m.id
               WHERE m.id_competition = $id_competition  AND mfu.assigned_user_id = $id_user ";

        $stmt = $this->db->query($sql);

        $status = self::STATUS_NEW;

        while ($row = $stmt->fetch()) {
            $status = self::STATUS_DONE;
            if (empty($row['status']) || $row['status'] == self::STATUS_IN_PROGRESS) {
                return self::STATUS_IN_PROGRESS;
            }

        }

        return $status;

    }

    public function updateTaskValues($data, $user_id, $assigned_user_id)
    {
        foreach($data as $key=> $record) {
            $rec['value'] = is_array($record) ? json_encode($record) : $record;
            $query = $this->db->query("SELECT id,type FROM tasks where name = '".$key."'");
            $query->execute();
            $row = $query->fetch();
            $task_id = $row['id'];
            $type = $row['type'];
            $rec['id_task'] = $task_id;
            $rec['id_user'] = $user_id;
            $rec['assigned_user_id'] = $assigned_user_id;
            $query = $this->db->query("SELECT id,value FROM task_answers WHERE id_task = '".$task_id."' AND id_user='".$user_id."' AND assigned_user_id='".$assigned_user_id."'");
            $query->execute();
            $row = $query->fetch();
            $record_id = is_array($row) ? $row['id']: false;
            $db_value = is_array($row) ? $row['value']: false;

            //var_dump($record_id);
            //var_dump($db_value);


            if (!$record_id) {
                if ($type == self::FIELD_NAME_INPUT_FILE ) {
                    $tab = [];
                if (isset($record['files']) && !empty($record['files'])){
                        foreach ($record['files'] as $file) {
                            $tab['links'][] = $file;
                        }
                        $rec['value'] = json_encode($tab);
                    }

                }
                $query = "INSERT INTO task_answers (id_task, id_user, value, assigned_user_id)
                          VALUES (:id_task, :id_user, :value, :assigned_user_id)";
            } else {
                if ($type == self::FIELD_NAME_INPUT_FILE ) {
                    $tab = [];
                    //var_dump($record);
                    if (isset($record['files']) && !empty($record['files'])){
                        if (Tools::isJson($db_value)) {
                            $tab = json_decode($db_value);
                        }

                        foreach ($record['files'] as $file) {
                            $tab->links[] = $file;
                        }
                        $rec['value'] = json_encode($tab);
                    } /*else {
                        if ($db_value !== false) {
                            $rec['value'] = $db_value;
                        }

                    }*/
                    /*if (Tools::isJson($db_value)) {
                        $tab = json_decode($db_value);
                    } else {
                        $tab[] = $db_value;
                    }
                    $tab[] = $record;
                    $rec['value'] = json_decode($tab);*/
                }
                $query = "UPDATE task_answers SET value = :value WHERE id_task = :id_task AND id_user = :id_user AND assigned_user_id = :assigned_user_id ";

            }



            $stmt = $this->db->prepare($query);
            $result = $stmt->execute($rec);
        }

        return true;
    }

    public function updateModuleStatus($id_module, $assigned_user_id, $modified_user_id, $status) {
        $data['date_modified'] = date('Y-m-d H:i:s');
        $data['status'] = $status;
        $data['id_module'] = $id_module;
        $data['assigned_user_id'] = $assigned_user_id;
        $data['modified_user_id'] = $modified_user_id;

        $query = "UPDATE module_flows_users
            SET status = :status,
                date_modified = :date_modified
            WHERE id_module = :id_module AND assigned_user_id = :assigned_user_id AND modified_user_id = :modified_user_id
            ";

        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($data);

        return true;
    }

    public function setNextStep($id_competition, $id_user, $modified_user)
    {

        $sql = "SELECT m.id, mfu.status, m.id_role,mfu.modified_user_id FROM modules m
               LEFT JOIN module_flows_users mfu ON (mfu.id_module = m.id AND mfu.assigned_user_id = $id_user)
               WHERE m.id_competition = $id_competition  ORDER BY sequence ASC";

        $stmt = $this->db->query($sql);
        $status_before = '';
        $modified_users_id = [];
        $modified_user_id = $modified_user->getId();
        $id_role = $modified_user->getRoleId();
        while ($row = $stmt->fetch()) {
            $modified_users_id[$row['modified_user_id']] = $row['modified_user_id'];
            if (empty($row['status']) && ($id_role == $row['id_role'] || $row['id_role'] == 0)) {
                if ($id_role == self::ROLE_CHILD && !in_array($status_before,[self::STATUS_IN_PROGRESS, self::STATUS_DONE])) {
                    $status_before = $row['status'];
                    continue;
                }
                $data['date_entered'] = date('Y-m-d H:i:s');
                $data['date_modified'] = date('Y-m-d H:i:s');
                $data['user_id'] = $id_user;
                $data['id_module'] = $row['id'];
                $data['modified_user_id'] = $modified_user_id;
                $data['status'] = self::STATUS_IN_PROGRESS;

                $query = "INSERT INTO module_flows_users (date_entered, date_modified, assigned_user_id, modified_user_id, id_module, status)
                          VALUES (:date_entered, :date_modified, :user_id, :modified_user_id, :id_module, :status)";

                $q = $this->db->prepare($query);
                $result = $q->execute($data);

                if ($row['id_role'] == 0) {
                    unset($modified_users_id[$modified_user_id]);
                    $guardian = array_shift($modified_users_id);
                    $data['modified_user_id'] = $guardian;
                    $query = "INSERT INTO module_flows_users (date_entered, date_modified, assigned_user_id, modified_user_id, id_module, status)
                          VALUES (:date_entered, :date_modified, :user_id, :modified_user_id, :id_module, :status)";

                    $q = $this->db->prepare($query);
                    $result = $q->execute($data);
                }
                break;
            }

            $status_before = $row['status'];

        }
    }

    public function loadModuleIdByModuleId($id) {
        $sql = "SELECT * FROM modules WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $data = $stmt->fetch();

        return $data;
    }

    public function startCompetition($id_competition, $id_user, $id_modified_user)
    {
        //przy starcie odpalamy wszystkie taski dla nauczyciela

        $sql = "SELECT id, id_role FROM modules m
               WHERE m.id_competition = $id_competition ORDER BY sequence ASC";

        $status = $this->checkStatus($id_competition, $id_user);

        $start_child = false;
        $start_teacher = false;

        if ($status == self::STATUS_NEW) {
            $stmt = $this->db->query($sql);
            while ($row = $stmt->fetch()) {
                if ($row['id_role'] == self::ROLE_TEACHER && !$start_teacher) {
                    $data['date_entered'] = date('Y-m-d H:i:s');
                    $data['date_modified'] = date('Y-m-d H:i:s');
                    $data['user_id'] = $id_user;
                    $data['modified_user_id'] = $id_modified_user;
                    $data['id_module'] = $row['id'];
                    $data['status'] = self::STATUS_IN_PROGRESS;
                    $query = "INSERT INTO module_flows_users (date_entered, date_modified, assigned_user_id, modified_user_id, id_module, status)
                          VALUES (:date_entered, :date_modified, :user_id, :modified_user_id, :id_module, :status)";

                    $q = $this->db->prepare($query);
                    $result = $q->execute($data);
                    $start_teacher = true;
                }

                if ($row['id_role'] == self::ROLE_CHILD && !$start_child) {
                    $data['date_entered'] = date('Y-m-d H:i:s');
                    $data['date_modified'] = date('Y-m-d H:i:s');
                    $data['user_id'] = $id_user;
                    $data['modified_user_id'] = $id_user;
                    $data['id_module'] = $row['id'];
                    $data['status'] = self::STATUS_IN_PROGRESS;
                    $query = "INSERT INTO module_flows_users (date_entered, date_modified, assigned_user_id, modified_user_id, id_module, status)
                          VALUES (:date_entered, :date_modified, :user_id, :modified_user_id, :id_module, :status)";

                    $q = $this->db->prepare($query);
                    $result = $q->execute($data);
                    $start_child = true;
                }

            }
        }


        return true;


    }

    public function prepareFields($value) {

        if (Tools::isInt($value)) {
            return (int) $value;
        }

        if (Tools::isJson($value)) {
            return json_decode($value);
        }

        return $value;
    }

    public function getReadyStepsForUser($assigned_user_id, $block_id) {
        $sql = "SELECT c.id FROM competitions c
                INNER JOIN blocks b ON c.parent_id = b.id
                WHERE b.id = $block_id";

        $stmt = $this->db->query($sql);

        $all = 0;
        $done = 0;

        while ($row = $stmt->fetch()) {
           $all++;
           $status = $this->checkStatus($row['id'], $assigned_user_id);

            if ($status == self::STATUS_DONE) {
                $done++;
            }

        }

        return $done.'/'.$all;

    }

    public function fetchAllCompetitionsByParentId($userId)
    {
        $sql = "SELECT id,username,firstName,lastName,subRole FROM oauth_users WHERE parent_id = $userId ORDER BY id ASC";
        $stmt = $this->db->query($sql);

        $results = [];
        while ($row = $stmt->fetch()) {
            $row['block1'] = $this->getReadyStepsForUser($row['id'], 1);
            $row['block2'] = $this->getReadyStepsForUser($row['id'], 2);
            $row['block3'] = $this->getReadyStepsForUser($row['id'], 3);
            $results[] = new User($row);
        }

        return $results;
    }

    public function getFaqs()
    {
        $sql = "SELECT value FROM faqs";
        $value = $this->db->query($sql)->fetchColumn();

        return $value;
    }


}
