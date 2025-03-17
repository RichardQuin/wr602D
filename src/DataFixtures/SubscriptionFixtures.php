<?php
// src/DataFixtures/SubscriptionFixtures.php
namespace App\DataFixtures;

use App\Entity\Subscription;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SubscriptionFixtures extends Fixture
{
public function load(ObjectManager $manager): void
{
$subscriptions = [
['name' => 'Free', 'description' => 'Abonnement gratuit', 'price' => 0, 'max_pdf' => 10],
['name' => 'Pro', 'description' => 'Abonnement Pro', 'price' => 9.99, 'max_pdf' => 100],
['name' => 'Entreprise', 'description' => 'Abonnement Entreprise', 'price' => 29.99, 'max_pdf' => 1000],
];

foreach ($subscriptions as $data) {
$subscription = new Subscription();
$subscription->setName($data['name']);
$subscription->setDescription($data['description']);
$subscription->setPrice($data['price']);
$subscription->setMaxPdf($data['max_pdf']);

$manager->persist($subscription);
}

$manager->flush();
}
}
