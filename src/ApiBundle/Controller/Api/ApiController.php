<?php
/**
 * Created by PhpStorm.
 * User: gurez
 * Date: 14.06.2016
 * Time: 14:39
 */

namespace ApiBundle\Controller\Api;


use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use ApiBundle\Entity\User;
use ApiBundle\Entity\Task;
use ApiBundle\Entity\Tasklist;
use ApiBundle\Entity\Privileges;
use ApiBundle\Entity\Log;
use ApiBundle\Entity\Groups;
use ApiBundle\Entity\GroupsList;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ApiController extends Controller
{
    /**
     * @Route("/")
     * @Method("GET")
     */
    public function apiAction(){
        return new Response('This is my first API!');
    }
    /**
     * @Route("/api/users/")
     * @Method("GET")
     */
    public function usersAction() {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('ApiBundle:User')->findAll();
        $data = array();
        foreach ($users as $user){
            array_push($data,array("id"=>$user->getId(),"name"=>$user->getUsername(),"email"=>$user->getEmail()));
        }
        $response = new Response();
        $response->setContent(json_encode($data));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("api/users/find")
     * @Method("GET")
     */
    public function userFindAction(){
        $name = $_GET['name'];
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('ApiBundle:User')->findBy(array('username'=>$name));
        $data = array();
        foreach ($users as $user){
            array_push($data,array("id"=>$user->getId(),"name"=>$user->getUsername(),"email"=>$user->getEmail()));
        }
        $response = new Response();
        $response->setContent(json_encode($data));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/users/{id}")
     * @Method("GET")
     */
    public function usersIdAction($id) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('ApiBundle:User')->find($id);
        $response = new Response();
        if(is_null($user)){
            $data = array();
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }else {
            $data = array("id"=>$user->getId(),"name"=>$user->getUsername(),"email"=>$user->getEmail());
            $response->setStatusCode(Response::HTTP_OK);
        }

        $response->setContent(json_encode($data));

        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/users")
     * @Method("POST")
     */
    public function users2Action(Request $request){
        $userManager  = $this->get('fos_user.user_manager');
        $user         = $userManager->createUser();
        $user->setEnabled(true);
        $name = $request->get("name");
        $password = $request->get("password");
        $email = $request->get("email");

        // We check if all parameters are set
        $code = 0;
        if ($name === null)                                        $code = 200;
        else if ($email    === null)                               $code = 201;
        else if ($password === null)                               $code = 202;
        else if ($userManager->findUserByUsername($name) !== null) $code = 203;
        else if ($userManager->findUserByEmail($email)   !== null) $code = 204;
        // We set parameters to user object
        if($code!=0){
            $response = new Response();
            $response->setContent( json_encode(["error"=>$code]));
            $response->setStatusCode(Response::HTTP_CONFLICT);
            $response->headers->set('Content-Type', 'application/json');
            return  $response;
        }
        $user->setUsername($name);
        $user->setEmail($email);
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $password_user = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($password_user);

        $clientManager = $this->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        //$client->setRedirectUris(array('http://localhost:8888/app_dev.php'));
        $client->setAllowedGrantTypes(array('password'));
        $clientManager->updateClient($client);

        // We save the user in the database
        $userManager->updateUser($user);
        $client_id = $client->getPublicId();
        $data = array("client_id"=>$client_id,"client_secret"=>$client->getSecret(),"grant_type"=>"password","username"=>$name,"password"=>$password);
        $response = new Response();
        $response->setContent( json_encode($data));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("api/users")
     * @Method("PUT")
     */
    public function userPutAction(Request $request){
        $user = $this->get('security.context')->getToken()->getUser();
        $name = $request->get("name");
        $password = $request->get("password");
        $email = $request->get("email");
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('ApiBundle:User')->find($user->getId());
        $user->setEmail($email);
        $user->setUsername($name);
        $encoder = $this->get('security.encoder_factory')->getEncoder($user);
        $password_user = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($password_user);
        $em->persist($user);
        $em->flush();
        $response = new Response();
        $response->setContent( json_encode(["name"=>$name,"email"=>$email,"password"=>$password]));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;

    }

    /**
     * @Route("api/users")
     * @Method("DELETE")
     */
    public function userDeleteAction(){
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $tokens = $em->getRepository('ApiBundle:AccessToken')->findBy(array('user' => $user));
        foreach ($tokens as $token){$em->remove($token);$em->flush();}
        $tokensRefresh = $em->getRepository('ApiBundle:RefreshToken')->findBy(array('user' => $user));
        foreach ($tokensRefresh as $token){$em->remove($token);$em->flush();}
        $userManager = $this->get('fos_user.user_manager');
        $userManager->deleteUser($user);
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/tasklist")
     * @Method("POST")
     */
    public function createListAction(Request $request) {
        $name = $request->get("name");
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $taskList = new Tasklist();
        $taskList->CreateNewList($userId,$name);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($taskList);
        $em->flush();
        $response = new Response();
        if($taskList->getId()>0){
            $response->setContent(json_encode(array("id"=>$taskList->getId(),"name"=>$name)));
            $privileges = new Privileges();
            $privileges->AddPrivileges($userId,$taskList->getId(),3);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($privileges);
            $em->flush();
            $log = new Log();
            $log->Add($userId,$taskList->getId(),"Создание списка: ".$name,$em);
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/tasklist")
     * @Method("GET")
     */
    public function getListAction(){
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        //$lists = $em->getRepository('ApiBundle:Tasklist')->findBy(array("userId"=>$userId));
        $query = $em->createQuery("SELECT t FROM ApiBundle:Tasklist t LEFT JOIN ApiBundle:Privileges p  WHERE t.id=p.taskListId WHERE p.userId='$userId' AND p.level>0");
        $lists = $query->getResult();
        $data = array();
        foreach ($lists as $list){
            array_push($data,array("id"=>$list->getId(),"name"=>$list->getName()));
        }
        $response = new Response();
        $response->setContent(json_encode($data));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/tasklist/{id}")
     * @Method("GET")
     */
    public function getListIdAction($id){
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT t FROM ApiBundle:Tasklist t LEFT JOIN ApiBundle:Privileges p  WHERE t.id=p.taskListId WHERE p.userId='$userId' AND p.level>0 AND t.id='$id'");
        $list = $query->getOneOrNullResult();
        $response = new Response();
        if(!is_null($list)){
            $query = $em->createQuery("SELECT t FROM ApiBundle:Task t WHERE t.taskListId='$id'");
            $tasks = $query->getResult();
            $response->setContent(json_encode($tasks));
            $response->setStatusCode(Response::HTTP_OK);
            $log = new Log();
            $log->Add($userId,$id,"Получить список",$em);
        }else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
            $response->setContent(json_encode(""));
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/tasklist/{id}")
     * @Method("DELETE")
     */
    public function deleteListIdAction($id){
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT t FROM ApiBundle:Tasklist t LEFT JOIN ApiBundle:Privileges p  WHERE t.id=p.taskListId WHERE p.userId='$userId' AND p.level=3 AND t.id='$id'");
        $list = $query->getOneOrNullResult();
        $response = new Response();
        if(!is_null($list)){
            $name = $list->getName();
            $em->remove($list);
            $em->flush();
            $log = new Log();
            $log->Add($userId,$id,"Удаление списка: ".$name,$em);
            $response->setStatusCode(Response::HTTP_OK);
        }else {
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/privileges")
     * @Method("POST")
     */
    public function createPrivilegiesAction(Request $request) {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $taskListId = $request->get("taskListId");
        $level = $request->get("level");
        $forId = $request->get("id");
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT t FROM ApiBundle:Tasklist t LEFT JOIN ApiBundle:Privileges p  WHERE t.id=p.taskListId WHERE p.userId='$userId' AND p.level>1 AND t.id='$taskListId'");
        $list = $query->getOneOrNullResult();
        $response = new Response();
        if((!is_null($list))&&($level<3)){
            $privileges = new Privileges();
            $privileges->AddPrivileges($forId,$taskListId,$level);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($privileges);
            $em->flush();
            $log = new Log();
            $log->Add($userId,$taskListId,"Установил привилегию для пользователя ".$forId." уровня: ".$level,$em);
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/privileges")
     * @Method("DELETE")
     */
    public function deletePrivilegiesAction(Request $request) {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $taskListId = $request->get("taskListId");
        $forId = $request->get("id");
        $response = new Response();
        if($forId==$userId){
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery("DELETE ApiBundle:Privileges p WHERE p.taskListId='$taskListId' AND p.userId='$forId'");
            $query->getResult();
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $em = $this->getDoctrine()->getManager();
            $query = $em->createQuery("SELECT p FROM ApiBundle:Privileges p WHERE p.taskListId='$taskListId' AND p.userId='$userId' AND p.level=3");
            $privilegies=$query->getOneOrNullResult();
            if(!is_null($privilegies)){
                $query = $em->createQuery("DELETE ApiBundle:Privileges p WHERE p.taskListId='$taskListId' AND p.userId='$forId'");
                $query->getResult();
                $log = new Log();
                $log->Add($userId,$taskListId,"Удалил привилегии у пользователя ".$forId,$em);

                $response->setStatusCode(Response::HTTP_OK);
            }else{
                $response->setStatusCode(Response::HTTP_CONFLICT);
            }
        }        
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/privileges")
     * @Method("GET")
     */
    public function getPrivilegiesAction() {
        $taskListId = $_GET['taskListId'];
        $forId = $_GET['id'];
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT p FROM ApiBundle:Privileges p WHERE p.taskListId='$taskListId' AND p.userId='$forId' ORDER BY p.level ASC");
        $privilegies=$query->getOneOrNullResult();
        $response = new Response();
        if(!is_null($privilegies)){
            $response->setContent(json_encode(array("level"=>$privilegies->getLevel())));
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/privileges")
     * @Method("PUT")
     */
    public function putPrivilegiesAction(Request $request) {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $taskListId = $request->get("taskListId");
        $level = $request->get("level");
        $forId = $request->get("id");
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT t FROM ApiBundle:Tasklist t LEFT JOIN ApiBundle:Privileges p  WHERE t.id=p.taskListId WHERE p.userId='$userId' AND p.level>1 AND t.id='$taskListId'");
        $list = $query->getOneOrNullResult();
        $response = new Response();
        if((!is_null($list))&&($level<3)){
            $privileges = $em->getRepository('ApiBundle:Privileges')->findBy(array("taskListId"=>$list->getId(),"userId"=>$forId));
            if(!is_null($privileges)){
                $privileges = $privileges[0];
                $privileges->AddPrivileges($forId,$taskListId,$level);
                $em->persist($privileges);
                $em->flush();
                $log = new Log();
                $log->Add($userId,$taskListId,"Изменил привилегии для пользователя ".$forId." уровня: ".$level,$em);

            }
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/privileges/all/{id}")
     * @Method("GET")
     */
    public function getUsersPrivilegiesAction($id) {
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT DISTINCT (u.id) as id, u.username, u.email, p.level FROM  ApiBundle:User u JOIN  ApiBundle:Privileges p WHERE p.userId=u.id AND p.taskListId='$id'");
        $privilegies=$query->getResult();
        $response = new Response();
        $data = array();
        if(!is_null($privilegies)){
            $response->setContent(json_encode($privilegies));
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/api/task")
     * @Method("POST")
     */
    public function createTask(Request $request){
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $taskListId = $request->get("taskListId");
        $name = $request->get("name");
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT t FROM ApiBundle:Tasklist t LEFT JOIN ApiBundle:Privileges p  WHERE t.id=p.taskListId WHERE p.userId='$userId' AND p.level>1 AND t.id='$taskListId'");
        $list = $query->getOneOrNullResult();
        $response = new Response();
        if((!is_null($list))&&($taskListId==$list->getId())){
            $task = new Task();
            $task->Create($taskListId,$name);
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($task);
            $em->flush();
            $log = new Log();
            $log->Add($userId,$taskListId,"Добавил задачу ".$name,$em);
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_CONFLICT);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/task")
     * @Method("DELETE")
     */
    public function deleteTaskAction(Request $request){
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $taskListId = $request->get('taskListId');
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT t FROM ApiBundle:Tasklist t LEFT JOIN ApiBundle:Privileges p  WHERE t.id=p.taskListId WHERE p.userId='$userId' AND p.level>1 AND t.id='$taskListId'");
        $list = $query->getOneOrNullResult();
        $response = new Response();
        if(!is_null($list)){

            $query = $em->createQuery("DELETE ApiBundle:Task t  WHERE t.id='$id' AND t.taskListId='$taskListId'");
            $query->getResult();
            $log = new Log();
            $log->Add($userId,$taskListId,"Задача ".$id." удалена",$em);
            $response->setStatusCode(Response::HTTP_OK);
        }else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/task")
     * @Method("PUT")
     */
    public function putTaskAction(Request $request){
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $taskListId = $request->get('taskListId');
        $id = $request->get('id');
        $checked = $request->get('checked');
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT t FROM ApiBundle:Tasklist t LEFT JOIN ApiBundle:Privileges p  WHERE t.id=p.taskListId WHERE p.userId='$userId'  AND t.id='$taskListId'");
        $list = $query->getOneOrNullResult();
        $response = new Response();
        if(!is_null($list)){
            $query = $em->createQuery("UPDATE ApiBundle:Task t SET t.checked='$checked' WHERE t.id='$id' AND t.taskListId='$taskListId'");
            $query->getResult();
            $response->setStatusCode(Response::HTTP_OK);
            $log = new Log();
            $log->Add($userId,$taskListId,"Задача ".$id.".Состояние: ".$checked,$em);

        }else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/logs/{id}")
     * @Method("GET")
     */
    public function getLogsAction($id) {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT l FROM ApiBundle:Log l WHERE l.listId='$id'");
        $logs=$query->getResult();
        $response = new Response();
        if(!is_null($logs)){
            $response->setContent(json_encode($logs));
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/groups")
     * @Method("POST")
     */
    public function addGroupsAction(Request $request){
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $name = $request->get('name');
        $em = $this->getDoctrine()->getManager();
        $groups = new Groups();
        $groups->Add($userId,$name,$em);
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/groups")
     * @Method("GET")
     */
    public function getGroupsAction() {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT g.id, g.name FROM ApiBundle:Groups g WHERE g.userId='$userId'");
        $groups=$query->getResult();
        $response = new Response();
        if(!is_null($groups)){
            $response->setContent(json_encode($groups));
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/groups/{id}")
     * @Method("DELETE")
     */
    public function deleteGroupsAction($id) {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("DELETE ApiBundle:Groups g WHERE g.userId='$userId' AND g.id='$id'");
        $query->getResult();
        $response = new Response();
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/groups/{id}")
     * @Method("POST")
     */
    public function addUsersGroupsAction($id,Request $request) {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $forId = $request->get("userId");
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT g FROM ApiBundle:Groups g WHERE g.userId='$userId' AND g.id='$id'");
        $gr = $query->getOneOrNullResult();
        $response = new Response();
        if(!is_null($gr)){
            $groupsList = new GroupsList();
            $groupsList->Add($forId,$id,$em);
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/groups/{id}")
     * @Method("PUT")
     */
    public function deleteUsersGroupsAction($id,Request $request) {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $forId = $request->get("userId");
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT g FROM ApiBundle:Groups g WHERE g.userId='$userId' AND g.id='$id'");
        $gr = $query->getOneOrNullResult();
        $response = new Response();
        if(!is_null($gr)){
            $query = $em->createQuery("DELETE ApiBundle:GroupsList g WHERE g.userId='$forId' AND g.groupId='$id'");
            $gr = $query->getOneOrNullResult();
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
    /**
     * @Route("/api/groups/{id}")
     * @Method("GET")
     */
    public function getUserGroupsAction($id) {
        $userId = $this->get('security.context')->getToken()->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery("SELECT u.id, u.username FROM ApiBundle:User u JOIN ApiBundle:GroupsList g WHERE u.id=g.userId WHERE g.groupId='$id'");
        $groups=$query->getResult();
        $response = new Response();
        if(!is_null($groups)){
            $response->setContent(json_encode($groups));
            $response->setStatusCode(Response::HTTP_OK);
        }else{
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }

}