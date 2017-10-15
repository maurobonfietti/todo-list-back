<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Users;
use Doctrine\ORM\EntityManager;

class UserRepository
{
    /** @var EntityManager */
    public $em;

    public function __construct($manager)
    {
        $this->em = $manager;
    }

    public function create($email, $name, $surname, $password)
    {
        $user = new Users();
        $user->setCreatedAt(new \DateTime("now"));
        $user->setRole('user');
        $user->setEmail($email);
        $user->setName($name);
        $user->setSurname($surname);
        $pwd = hash('sha256', $password);
        $user->setPassword($pwd);
        $this->em->persist($user);
        $this->em->flush();
        $data = [
            'status' => 'success',
            'code' => 201,
            'msg' => 'Usuario creado.',
            'user' => $user,
        ];

        return $data;
    }
}