<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\MicroPost;
use App\Entity\UserPreferences;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
	private $passwordEncoder;

	private const USERS = [
        [
            'username' => 'john_doe',
            'email' => 'john_doe@doe.com',
            'password' => 'john12345',
            'fullName' => 'John Doe',
            'roles'    => [User::ROLE_USER],
        ],
        [
            'username' => 'rob_smith',
            'email' => 'rob_smith@smith.com',
            'password' => 'rob12345',
            'fullName' => 'Rob Smith',
            'roles'    => [User::ROLE_USER],
        ],
        [
            'username' => 'marry_gold',
            'email' => 'marry_gold@gold.com',
            'password' => 'marry12345',
            'fullName' => 'Marry Gold',
            'roles'    => [User::ROLE_USER],
        ],
        [
            'username' => 'superadmin',
            'email' => 'super@admin.com',
            'password' => 'superadmin',
            'fullName' => 'Super Admin',
            'roles'    => [User::ROLE_ADMIN],
        ],
    ];

	private const POST_TEXT = [
        'Hello, how are you?',
        'It\'s nice sunny weather today',
        'I need to buy some ice cream!',
        'I wanna buy a new car',
        'There\'s a problem with my phone',
        'I need to go to the doctor',
        'What are you up to today?',
        'Did you watch the game yesterday?',
        'How was your day?'
    ];

    private const LANGUAGES = 'en';

	public function __construct(UserPasswordEncoderInterface $passwordEncoder)
	{
		$this->passwordEncoder = $passwordEncoder;
	}

    public function load(ObjectManager $manager)
    {
     	$this->loadUsers($manager);
     	$this->loadMicroPost($manager);
    }

    private function loadMicroPost(ObjectManager $manager)
    {
    	for($i = 0; $i < 30; $i++)
        {
        	$micropost = new MicroPost();
        	$micropost->setText(
        		self::POST_TEXT[rand(0, count(self::POST_TEXT) - 1)]
        	);
        	$date = new \Datetime();
        	$date->modify('-'.rand(0,10).' day');
        	$micropost->setTime($date);
        	$micropost->setUser($this->getReference(
        		self::USERS[rand(0, count(self::USERS) - 1)]['username']
        	));
        	$manager->persist($micropost);
        }

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager)
    {
    	foreach(self::USERS as $userData)
    	{
	    	$user = new User();
	    	$user->setUsername($userData['username']);
	    	$user->setFullname($userData['fullName']);
	    	$user->setEmail($userData['email']);
	    	$user->setPassword(
	    		$this->passwordEncoder->encodePassword($user,$userData['password'])
	    	);
	    	$user->setRoles($userData['roles']);
	    	$this->addReference($userData['username'], $user);
            $user->setEnabled(true);

            $preferences = new UserPreferences();
            $preferences->setLocale(self::LANGUAGES);
            $user->setPreferences($preferences);
            $manager->persist($preferences);
	    	$manager->persist($user);	
    	}
    	
    	$manager->flush();
    }
}
