<?php
/**
 * Created by PhpStorm.
 * User: gurez
 * Date: 15.06.2016
 * Time: 20:16
 */

namespace ApiBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * @ORM\Entity
 * @ORM\Table(name="groupslist")
 */

class GroupsList
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
    public $groupId;
    /**
     * @ORM\Column(type="integer")
     */
    public $userId;
    public function Add($_userid,$_groupId,$em){
        $this->userId = $_userid;
        $this->groupId = $_groupId;
        $em->persist($this);
        $em->flush();
    }

}