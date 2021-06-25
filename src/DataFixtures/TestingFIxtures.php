<?php
/**
 * Created by PhpStorm.
 * User: brainstream
 * Date: 16/6/21
 * Time: 7:33 PM
 */

namespace  App\DataFixtures;

use App\Entity\Testing;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;

class TestingFIxtures extends Fixture implements FixtureGroupInterface
{
    public  const admin_name = "aakanksha";

    public static function getGroups(): array
    {
        // TODO: Implement getGroups() method.
        return ['group1', 'group2'];
    }

    public function load(ObjectManager $manager)
    {
        // TODO: Implement load() method.
        for ($i = 27; $i < 35; $i++) {
            $test = new Testing();
            $test->setTitle('product '.$i);
            $test->setDescription("this is the description");
            $test->setAge(rand(2,4));

            $manager->persist($test);
        }
        $manager->flush();

        $this->addReference('admin_name', "aakanksha");
    }
}