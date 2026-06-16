<?php

namespace App\DataFixtures;

use App\Entity\ServiceType;
use App\Entity\Tariff;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ServiceTypeFixture extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['all'];
    }

    public function load(ObjectManager $manager): void
    {
        $services = [
            [
                'code' => 'regular',
                'name' => 'Regular Shipping',
                'weightSurcharge' => 1.0,
                'dimensionalSurcharge' => 1.0,
                'priorityMultiplier' => 1.0,
                'reducesEstimatedDeliveryDays' => 0,
                'volumeDivisor' => 5000,
                'maxWeight' => 30.0,
                'maxDimension' => 150.0,
                'tariffs' => [
                    ['min' => 0.0, 'max' => 1.0, 'price' => 3.5],
                    ['min' => 1.0, 'max' => 2.0, 'price' => 4.0],
                    ['min' => 2.0, 'max' => 5.0, 'price' => 4.49],
                    ['min' => 5.0, 'max' => 10.0, 'price' => 7.0],
                ],
            ],
            [
                'code' => 'priority',
                'name' => 'Priority Shipping',
                'weightSurcharge' => 1.2,
                'dimensionalSurcharge' => 1.1,
                'priorityMultiplier' => 1.5,
                'reducesEstimatedDeliveryDays' => 2,
                'volumeDivisor' => 4000,
                'maxWeight' => 30.0,
                'maxDimension' => 150.0,
                'tariffs' => [
                    ['min' => 0.0, 'max' => 1.0, 'price' => 4.0],
                    ['min' => 1.0, 'max' => 2.0, 'price' => 5.5],
                    ['min' => 2.0, 'max' => 5.0, 'price' => 7.5],
                    ['min' => 5.0, 'max' => 10.0, 'price' => 11.0],
                ],
            ],
            [
                'code' => 'express',
                'name' => 'Express Delivery',
                'weightSurcharge' => 1.5,
                'dimensionalSurcharge' => 1.5,
                'priorityMultiplier' => 2.0,
                'reducesEstimatedDeliveryDays' => 3,
                'volumeDivisor' => 3000,
                'maxWeight' => 30.0,
                'maxDimension' => 150.0,
                'tariffs' => [
                    ['min' => 0.0, 'max' => 1.0, 'price' => 5.0],
                    ['min' => 1.0, 'max' => 2.0, 'price' => 7.0],
                    ['min' => 2.0, 'max' => 5.0, 'price' => 10.0],
                    ['min' => 5.0, 'max' => 10.0, 'price' => 15.0],
                ],
            ],
        ];

        foreach ($services as $data) {
            $service = new ServiceType();
            $service->setCode($data['code'])
                ->setName($data['name'])
                ->setWeightSurcharge($data['weightSurcharge'])
                ->setDimensionalSurcharge($data['dimensionalSurcharge'])
                ->setPriorityMultiplier($data['priorityMultiplier'])
                ->setReducesEstimatedDeliveryDays($data['reducesEstimatedDeliveryDays'])
                ->setVolumeDivisor($data['volumeDivisor'])
                ->setMaxWeight($data['maxWeight'])
                ->setMaxDimension($data['maxDimension']);

            $manager->persist($service);

            foreach ($data['tariffs'] as $tariffData) {
                $tariff = new Tariff();
                $tariff->setMinWeight($tariffData['min'])
                    ->setMaxWeight($tariffData['max'])
                    ->setBasePrice($tariffData['price'])
                    ->setServiceType($service);

                $manager->persist($tariff);

                $service->addTariff($tariff);
            }
        }

        $manager->flush();
    }
}
