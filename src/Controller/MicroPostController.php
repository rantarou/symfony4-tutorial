<?php 

namespace App\Controller;

use App\Entity\User;
use App\Entity\MicroPost;
use App\Form\MicroPostType;
use App\Repository\UserRepository;
use App\Repository\MicroPostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/micro-post")
 */
class MicroPostController
{
	private $twig;
	private $microPostRepository;
	private $userRepository;
	private $formFactory;
	private $entityManager;
	private $router;
	private $flashBag;
	private $authorizationChecker;

	public function __construct(
		\Twig_Environment $twig, 
		MicroPostRepository $microPostRepository, 
		UserRepository $userRepository,
		FormFactoryInterface $formFactory, 
		EntityManagerInterface $entityManager,
		RouterInterface $router,
		FlashBagInterface $flashBag,
		AuthorizationCheckerInterface $authorizationChecker

	)
	{
		$this->twig                 = $twig;
		$this->microPostRepository  = $microPostRepository;
		$this->userRepository       = $userRepository;
		$this->formFactory          = $formFactory;
		$this->entityManager        = $entityManager;
		$this->router               = $router;
		$this->flashBag             = $flashBag;
		$this->authorizationChecker = $authorizationChecker;
	}

	/**
	 * @Route("/", name="micro-post_index")
	 */
	public function index(TokenStorageInterface $tokenStorage)
	{
		$currentUser = $tokenStorage->getToken()->getUser();
		$usersToFollow = [];

		if($currentUser instanceof User)
		{
			$posts = $this->microPostRepository->findAllByUsers(
				$currentUser->getFollowing()
			);

			$usersToFollow = count($posts) === 0 ?
				$this->userRepository->findAllWithMoreThan5PostsExceptUser($currentUser) : [];
		} else {
			$posts = $this->microPostRepository->findBy(
				[],
				['time' => 'DESC']
			);
		}

		$html = $this->twig->render('micro-post/index.html.twig',[
			'posts' => $posts,
			'usersToFollow' => $usersToFollow
		]);

		return new Response($html);
	}

	/**
	 * @Route("/delete/{id}",name="micro-post_delete")
	 * @Security("is_granted('delete', micropost)", message="Access denied")
	 */
	public function delete(MicroPost $micropost)
	{
		// if(!$this->authorizationChecker->isGranted('delete', $micropost)) {
		// 	throw new UnauthorizedHttpException();
		// }

		$this->entityManager->remove($micropost);
		$this->entityManager->flush();

		$this->flashBag->add('notice','Micro Post was deleted');

		return new RedirectResponse(
			$this->router->generate('micro-post_index')
		);
	}

	/**
	 * @Route("/edit/{id}", name="micro-post_edit")
	 * @Security("is_granted('edit', micropost)", message="Access denied")
	 */
	public function edit(MicroPost $micropost, Request $request)
	{
		// if(!$this->authorizationChecker->isGranted('edit', $micropost)) {
		// 	throw new UnauthorizedHttpException();
		// }

		$form = $this->formFactory->create(MicroPostType::class, $micropost);
		$form->handleRequest($request);

		$micropost->setTime(new \Datetime());

		if($form->isSubmitted() && $form->isValid())
		{
			$this->entityManager->flush();

			$this->flashBag->add('notice','Micro Post Updated');

			return new RedirectResponse(
				$this->router->generate('micro-post_index')
			);
		}

		return new Response(
			$this->twig->render('micro-post/add.html.twig',[
				'form' => $form->createView()
			])
		);
	}

	/**
	 * @Route("/add", name="micro-post_add")
	 * @Security("is_granted('ROLE_USER')")
	 */
	public function add(Request $request, TokenStorageInterface $tokenStorage)
	{
		$user = $tokenStorage->getToken()->getUser();

		$micropost = new MicroPost();
		//$micropost->setTime(new \Datetime());
		$micropost->setUser($user);

		$form = $this->formFactory->create(MicroPostType::class, $micropost);
		$form->handleRequest($request);

		if($form->isSubmitted() && $form->isValid())
		{
			$this->entityManager->persist($micropost);
			$this->entityManager->flush();

			$this->flashBag->add('notice','Micro Post Added');

			return new RedirectResponse($this->router->generate('micro-post_index'));
		}

		return new Response(
			$this->twig->render('micro-post/add.html.twig',[
				'form' => $form->createView()
			])
		);
	}

	/**
	 * @Route("/user/{username}", name="micro-post_user")
	 */
	public function userPosts(User $userWithPosts)
	{
		$html = $this->twig->render('micro-post/user-posts.html.twig',[
			'posts' => $this->microPostRepository->findBy(
				['user' => $userWithPosts],
				['time' => 'DESC']
			),
			'user' => $userWithPosts
			//'posts' => $userWithPosts->getPosts()
		]);

		return new Response($html);
	}

	/**
	 * @Route("/{id}", name="micro-post_post")
	 */
	public function post(MicroPost $post)
	{
		return new response(
			$this->twig->render(
				'micro-post/post.html.twig',[
					'post' => $post
				]
			)
		);
	}	
}