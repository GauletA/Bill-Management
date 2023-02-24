<?php

namespace App\DataFixtures;

use App\Entity\Customer;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Nelmio\Alice\Loader\NativeLoader;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    public function __construct(private UserPasswordHasherInterface $encoder)
    {}

    private function getCustomers(User $user) {
        $loader = new NativeLoader();
        $tab = [];
        for($i = 0; $i < random_int(2, 4); $i++) {
            $customer = $loader->loadFile(__DIR__.'/../../fixtures/Customer.yml')->getObjects()['customer_' . random_int(1, 40)];
            $customer->setUser($user);
            $tab[] = $customer;
        }
        return $tab;
    }

    private function getUsers() {
        $loader = new NativeLoader();
        return $loader->loadFile(__DIR__.'/../../fixtures/User.yml')->getObjects();
    }

    private function hashPassword($user) {
        $hash = $this->encoder->hashPassword($user, $user->getPassword());
        $user->setPassword($hash);
    }

    private function getRandomInvoice(Customer $customer) {
        $loader = new NativeLoader();
        $tab = [];
        for($i = 0; $i < random_int(2, 10); $i++) {
            $invoice = $loader->loadFile(__DIR__.'/../../fixtures/Invoice.yml')->getObjects()['invoice_' . random_int(1, 40)];
            $invoice->setChrono($i+1);
            $invoice->setCustomer($customer);
            $tab[] = $invoice;
        }
        return $tab;
    }

    public function load(ObjectManager $manager): void
    {
        $users = $this->getUsers();
        foreach ($users as $user) {
            $this->hashPassword($user);
            $manager->persist($user);
            foreach ($this->getCustomers($user) as $customer){
                $manager->persist($customer);
                foreach($this->getRandomInvoice($customer) as $invoice) {
                    $manager->persist($invoice);
                }
            }
        }

        $manager->flush();
    }
}
