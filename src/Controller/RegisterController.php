<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Event\UserRegisterEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Security\TokenGenerator;

class RegisterController extends Controller
{
	/**
	 * @Route("/register",name="user_register")
	 */
	public function register(
		UserPasswordEncoderInterface $passwordEncoder, 
		Request $request,
		EventDispatcherInterface $eventDispatcher,
		TokenGenerator $tokenGenerator
	)
	{
		$user = new User();
		$form = $this->createForm(UserType::class, $user);
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid())
		{
			$password = $passwordEncoder->encodePassword(
				$user,
				$user->getPlainPassword()
			);
			$user->setPassword($password);
			$user->setConfirmationToken($tokenGenerator->getRandomSecureToken(30));

			$em = $this->getDoctrine()->getManager();
			$em->persist($user);
			$em->flush();

			$userRegisterEvent = new UserRegisterEvent($user);

			$eventDispatcher->dispatch(
				UserRegisterEvent::NAME, 
				$userRegisterEvent
			);

			$this->redirectToRoute('micro-post_index');
		}

		return $this->render('security/register.html.twig',[
			'form' => $form->createView()
		]);
	}
}