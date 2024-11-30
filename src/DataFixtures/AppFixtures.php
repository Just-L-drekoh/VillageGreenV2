<?php

namespace App\DataFixtures;

use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        try {
            $faker = \Faker\Factory::create('fr_FR');
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la création de Faker', 0, $e);
        }

        #Creation de services lier a des utilisateurs

        $servicetype = ['apres-vente', 'commercial', 'equipe'];

        for ($i = 0; $i < 10; $i++) {
            try {
                $service = new \App\Entity\Service();
                $service->setType($servicetype[mt_rand(0, 2)]);
                $manager->persist($service);
            } catch (\Exception $e) {
                throw new \RuntimeException('Erreur lors de la création d\'un service', 0, $e);
            }
        }

        try {
            $manager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la sauvegarde des services', 0, $e);
        }

        # Creation de l'utilisateur admin

        try {
            $user = new \App\Entity\User();
            $user->setEmail('admin@admin.com');
            $user->setPassword(password_hash('password', PASSWORD_DEFAULT));
            $user->setFirstName('Admin');
            $user->setLastName('Admin');
            $user->setPhone('0000000000');
            $user->setLastConnect(new \DateTimeImmutable());
            $user->setVerified(true);
            $user->setRef("Cli:00000");
            $user->setRoles(['ROLE_ADMIN']);

            $manager->persist($user);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la création de l\'utilisateur admin', 0, $e);
        }

        # Creation de 9 Utilisateurs dans la BDD
        for ($i = 0; $i < 9; $i++) {
            try {
                $user = new \App\Entity\User();
                $user->setEmail($faker->email);
                $user->setPassword(password_hash('password', PASSWORD_DEFAULT));
                $user->setFirstName($faker->firstName);
                $user->setLastName($faker->lastName);
                $user->setPhone($faker->phoneNumber);
                $user->setLastConnect(new \DateTimeImmutable());
                $user->setVerified(false);
                $user->setRef("Cli:" . mt_rand(10000, 99999));
                $user->setRoles(['ROLE_USER']);
                $user->setService($faker->randomElement($manager->getRepository(\App\Entity\Service::class)->findAll()));
                $manager->persist($user);
            } catch (\Exception $e) {
                throw new \RuntimeException('Erreur lors de la création d\'un utilisateur', 0, $e);
            }
        }

        try {
            $manager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la sauvegarde des utilisateurs', 0, $e);
        }

        # Creation de 10 adresses dans la BDD

        $addressType = ['Livraison', 'Facturation'];
        for ($i = 0; $i < 10; $i++) {
            try {
                $address = new \App\Entity\Address();
                $address->setAddress($faker->streetAddress);
                $address->setCity($faker->city);
                $address->setCp($faker->postcode);
                $address->setType($faker->randomElement($addressType));
                $address->setUser($faker->randomElement($manager->getRepository(\App\Entity\User::class)->findAll()));
                $address->setComplement($faker->buildingNumber);
                $manager->persist($address);
            } catch (\Exception $e) {
                throw new \RuntimeException('Erreur lors de la création d\'une adresse', 0, $e);
            }
        }

        try {
            $manager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la sauvegarde des adresses', 0, $e);
        }

        # Creation de 5 Details de Fournisseurs dans la BDD
        $suppliertype = ['constructeur', 'importateur'];
        for ($i = 0; $i < 5; $i++) {
            try {
                $provider = new \App\Entity\SupplierDetails();
                $provider->setType($faker->randomElement($suppliertype));
                $provider->setStatus('Active');
                $provider->setRef("Fou:" . mt_rand(10000, 99999));
                $provider->setUser($faker->randomElement($manager->getRepository(\App\Entity\User::class)->findAll()));
                $manager->persist($provider);
            } catch (\Exception $e) {
            }
        }

        try {
            $manager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la sauvegarde des fournisseurs', 0, $e);
        }

        // Cette partie concerne tout ce qui se rapporte aux instruments de musique
        // (images, produits, rubriques/sousrubriques)

        # Creation de Rubrique dans la BDD

        $NameRubriques = ['vent', 'percussions', 'cordes'];

        foreach ($NameRubriques as $label) {
            try {
                $rubrique = new \App\Entity\Rubric();
                $rubrique->setLabel($label);
                $rubrique->setSlug($label);
                $rubrique->setImage($faker->imageUrl);
                $rubrique->setContent($faker->paragraph);


                $manager->persist($rubrique);
            } catch (\Exception $e) {
                throw new \RuntimeException('Erreur lors de la création d\'une rubrique', 0, $e);
            }
        }

        try {
            $manager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la sauvegarde des rubriques', 0, $e);
        }


        # Creation de Sousrubrique dans la BDD
        $NameSubRubriques = ['batterie', 'guitare', 'piano', 'flute'];

        foreach ($NameSubRubriques as $label) {
            try {
                $subrubrique = new \App\Entity\Rubric();
                $subrubrique->setLabel($label);
                $subrubrique->setSlug($label);
                $subrubrique->setImage($faker->imageUrl);
                $subrubrique->setContent($faker->paragraph);
                $subrubrique->setParent($faker->randomElement($manager->getRepository(\App\Entity\Rubric::class)->findAll()));

                $manager->persist($subrubrique);
            } catch (\Exception $e) {
                throw new \RuntimeException('Erreur lors de la création d\'une sousrubrique', 0, $e);
            }

            try {
                $manager->flush();
            } catch (\Exception $e) {
                throw new \RuntimeException('Erreur lors de la sauvegarde des sousrubriques', 0, $e);
            }
        }

        # Creation d'une taxe dans la BDD
        try {
            $tax = new \App\Entity\Tax();
            $tax->setRate('18.60');
            $manager->persist($tax);
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la creation d\'une taxe', 0, $e);
        }

        try {
            $manager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la sauvegarde de la taxe', 0, $e);
        }

        # Creation de Produit dans la BDD
        for ($i = 0; $i < 10; $i++) {
            try {
                $product = new \App\Entity\Product();
                $product->setLabel($faker->sentence);
                $product->setSlug($faker->slug);
                $product->setStock(mt_rand(1, 100));
                $product->setPrice(mt_rand(1, 100));
                $product->setRef("Inst:" . mt_rand(10000, 99999));
                $product->setContent($faker->paragraph);
                $product->setWeight($faker->randomFloat(2, 0, 100));
                $product->setSupplier($faker->randomElement($manager->getRepository(\App\Entity\SupplierDetails::class)->findAll()));
                $product->setRubric($faker->randomElement($manager->getRepository(\App\Entity\Rubric::class)->findAll()));
                $product->setTax($faker->randomElement($manager->getRepository(\App\Entity\Tax::class)->findAll()));
                $product->setCreatedAt(new DateTimeImmutable());
                $product->setUpdatedAt(new DateTimeImmutable());
                $manager->persist($product);
            } catch (\Exception $e) {
                throw new \RuntimeException('Erreur lors de la création d\'un produit', 0, $e);
            }
        }

        try {
            $manager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la sauvegarde des produits', 0, $e);
        }


        # Creation d'image dans la BDD
        for ($i = 0; $i < 10; $i++) {
            try {
                $image = new \App\Entity\Image();
                $image->setImg($faker->imageUrl);
                $image->setProduct($faker->randomElement($manager->getRepository(\App\Entity\Product::class)->findAll()));
                $manager->persist($image);
            } catch (\Exception $e) {
                throw new \RuntimeException('Erreur lors de la création d\'une image', 0, $e);
            }
        }

        try {
            $manager->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de la sauvegarde des images', 0, $e);
        }
    }
}
