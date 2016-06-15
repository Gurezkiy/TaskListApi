<?php
/**
 * Created by PhpStorm.
 * User: gurez
 * Date: 13.06.2016
 * Time: 18:54
 */
namespace AppBundle\Controller\Api;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;

/**
 * @Route("/api/users")
 */
class UsersController extends Controller
{
    /**
     * @Route("/")
     * @Method("POST")
     */
    public function registerAction($name,$email,$password){
        $user = new user();
        $user->Register($name,$email,$password);
        $em = $this->getDoctrine()->getEntityManager();
        $em->persist($user);
        $em->flush();

        $response = new Response();
        $response->setContent( json_encode('User is created. ID:' . $user->getId()));
        $response->setStatusCode(Response::HTTP_OK);
        $response->headers->set('Content-Type', 'application/json');
        return  $response;
    }
}