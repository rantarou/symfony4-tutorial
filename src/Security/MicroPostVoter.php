<?php 

namespace App\Security;

use App\Entity\MicroPost;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;

class MicroPostVoter extends Voter
{
	const EDIT = 'edit';
	const DELETE = 'delete';

	private $decisionManager;

	public function __construct(AccessDecisionManagerInterface $decisionManager)
	{
		$this->decisionManager = $decisionManager;
	}

	protected function supports($attribute, $subject)
	{
		if(!in_array($attribute, [self::EDIT, self::DELETE])) {
			return false;
		}

		if (!$subject instanceof Micropost) {
			return false;
		}

		return true;
	}

	protected function voteOnAttribute($attribute, $subject, TokenInterface $token) 
	{
		if($this->decisionManager->decide($token, [User::ROLE_ADMIN])) {
			return true;
		}

		$authenticatedUser = $token->getUser();

		if(!$authenticatedUser instanceof User) {
			return false;
		}

		$microPost = $subject;

		return $microPost->getUser()->getId() === $authenticatedUser->getId();
	}
}