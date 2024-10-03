<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Создаем пользователя-администратора
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setEmail('admin@gmail.com');
        $admin->setPassword(password_hash('admin', PASSWORD_BCRYPT)); // Убедитесь, что используете безопасный хеш
        $admin->setRoles(['ROLE_ADMIN']); // Замените на вашу систему ролей

        $manager->persist($admin);
        $manager->flush();
    }
}
