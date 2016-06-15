<?php
/**
 * Created by PhpStorm.
 * User: gurez
 * Date: 15.06.2016
 * Time: 3:17
 */

namespace ApiBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="privileges")
 */
class Privileges
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="integer")
     */
    protected $userId;
    /**
     * @ORM\Column(type="integer")
     */
    protected $taskListId;
    /**
     * @ORM\Column(type="integer")
     */
    protected $level;

    public  function AddPrivileges($_userId,$_listid,$_level){
        $this->userId = $_userId;
        $this->taskListId = $_listid;
        $this->level = $_level;
    }
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return Privileges
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set taskListId
     *
     * @param integer $taskListId
     * @return Privileges
     */
    public function setTaskListId($taskListId)
    {
        $this->taskListId = $taskListId;

        return $this;
    }

    /**
     * Get taskListId
     *
     * @return integer 
     */
    public function getTaskListId()
    {
        return $this->taskListId;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return Privileges
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }
}
