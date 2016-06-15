<?php
/**
 * Created by PhpStorm.
 * User: gurez
 * Date: 15.06.2016
 * Time: 17:31
 */

namespace ApiBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="logs")
 */
class Log
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
    public $action;
    /**
     * @ORM\Column(type="integer")
     */
    public $userId;
    /**
     * @ORM\Column(type="integer")
     */
    public $listId;

    public function Add($_userId,$_listId,$_action,$em){
        $this->userId = $_userId;
        $this->action = $_action;
        $this->listId = $_listId;
        $em->persist($this);
        $em->flush();
    }
    

}