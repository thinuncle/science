<?php
namespace Science;

use Monolog\Logger;
use Science\User;

class UserMapper
{
    protected $logger;
    protected $db;
    CONST ROLE_PRINCIPLE = 'principle';
    CONST ROLE_GUARDIAN = 'guardian';

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
    public function fetchAllByParentId($userId)
    {
        $sql = "SELECT id,username,firstName,lastName,subRole FROM oauth_users WHERE parent_id = $userId ORDER BY id ASC";
        $stmt = $this->db->query($sql);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = new User($row);
        }

        return $results;
    }

    /**
     * Load a single author
     *
     * @return Author|false
     */
    public function loadById($id)
    {
        $sql = "SELECT * FROM oauth_users WHERE id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $id]);
        $data = $stmt->fetch();

        if ($data) {
            unset($data['password']);
            return new User($data);
        }

        return false;
    }

    public function loadByUser($username)
    {
        $sql = "SELECT * FROM oauth_users WHERE username = :username";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['username' => $username]);
        $data = $stmt->fetch();


        if ($data) {
            return new User($data);
        }

        return false;
    }

    /**
     * Create an author
     *
     * @return Author
     */
    public function insert(User $user)
    {
        $data = $user->getArrayCopy();
        unset($data['id']);
        unset($data['block1']);
        unset($data['block2']);
        unset($data['block3']);
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['role_id'] = !empty($data['parent_id']) ? 1 : 2;

        $query = "INSERT INTO oauth_users (username, password, role_id, parent_id, firstName, lastName, subRole)
            VALUES (:username, :password, :role_id, :parent_id, :firstName, :lastName, :subRole)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($data);

        return new User($data);
    }

    /**
     * Update an author
     *
     * @return Author
     */
    public function update(User $author)
    {
        /*$data = $author->getArrayCopy();
        $data['updated'] = date('Y-m-d H:i:s');

        $query = "UPDATE author
            SET name = :name,
                biography = :biography,
                date_of_birth = :date_of_birth,
                created = :created,
                updated = :updated
            WHERE author_id = :author_id
            ";

        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($data);

        return new Author($data);*/
    }

    /**
     * Delete an author
     *
     * @param $id       Id of author to delete
     * @return boolean  True if there was an author to delete
     */
    public function delete($id)
    {
       /* $data['author_id'] = $id;
        $query = "DELETE FROM author WHERE author_id = :author_id";

        $stmt = $this->db->prepare($query);
        $stmt->execute($data);

        return (bool)$stmt->rowCount();*/
    }
}
