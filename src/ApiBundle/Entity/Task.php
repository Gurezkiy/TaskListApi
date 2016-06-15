<?php
/**
 * Created by PhpStorm.
 * User: gurez
 * Date: 15.06.2016
 * Time: 15:50
 */

namespace ApiBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="tasks")
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(type="text")
     */
    public $name;
    /**
     * @ORM\Column(type="integer")
     */
    public $taskListId;
    /**
     * @ORM\Column(type="boolean")
     */
    public $checked;

    public function Create($_listid,$_name){
        $this->taskListId = $_listid;
        $this->name = $_name;
        $this->checked = false;
    }
}