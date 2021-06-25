<?php

namespace App\DataFixtures;

use App\Entity\Testing;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // create 20 products! Bam!
//        for ($i = 27; $i < 35; $i++) {
//            $test = n1ew Testing();
//            $test->setTitle('product '.$i);
//            $test->setDescription("this is the description");
//            $test->setAge(rand(2,4));
//
//            $manager->persist($test);
//        }
       // $const_value = $this->

        $user = new User();
        $user->setEmail("aashivinay@gail.com");
        $user->setRoles(["ROLE_USER"]);

        $password = $this->encoder->encodePassword($user, 'admin123');
        $user->setPassword($password);

        $manager->persist($user);

        $manager->flush();
    }
}
