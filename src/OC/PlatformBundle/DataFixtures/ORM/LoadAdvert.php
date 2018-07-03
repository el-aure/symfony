<?php
// src/OC/PlatformBundle/DataFixtures/ORM/LoadAdvert.php

namespace OC\PlatformBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\Category;
use OC\PlatformBundle\Entity\AdvertSkill;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use OC\PlatformBundle\DataFixtures\ORM\LoadCategory;
use OC\PlatformBundle\DataFixtures\ORM\LoadSkill;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;

class LoadAdvert extends AbstractFixture implements OrderedFixtureInterface
{
  // Dans l'argument de la méthode load, l'objet $manager est l'EntityManager
  public function load(ObjectManager $manager)
  {
    // Liste des noms de catégorie à ajouter
    $listAdverts = array(
      array(
        'title'   => 'Recherche développpeur Symfony',
        'author'  => 'Alexandre@toto.com',
        'content' => 'Nous recherchons un développeur Symfony débutant sur Lyon. Blabla…',
        'date'    => new \Datetime(),
        'updated_at'    => new \Datetime(date('Y') . '-' . date('m') . '-' . (date('d') - 3))
       ),
      array(
        'title'   => 'Mission de webmaster',
        'author'  => 'Hugo@toto.com',
        'content' => 'Nous recherchons un webmaster capable de maintenir notre site internet. Blabla…',
        'date'    => new \Datetime(),
        'updated_at'    => new \Datetime(date('Y') . '-' . date('m') . '-' . (date('d') - 2))
       ),
      array(
        'title'   => 'Offre de stage webdesigner',
        'author'  => 'Mathieu@toto.com',
        'content' => 'Nous proposons un poste pour webdesigner. Blabla…',
        'date'    => new \Datetime(),
        'updated_at'    => new \Datetime(date('Y') . '-' . date('m') . '-' . (date('d') - 4))
       )
    );

	// On récupère toutes les compétences possibles
	$listSkills = $manager->getRepository('OCPlatformBundle:Skill')->findAll();

	// La méthode findAll retourne toutes les catégories de la base de données
	$listCategories = $manager->getRepository('OCPlatformBundle:Category')->findAll();

	$comptAdvert = 0;
    foreach ($listAdverts as $arrAdvert) {
		$comptAdvert++;
		// On crée la catégorie
		$advert = new Advert();
		$advert->setTitle($arrAdvert['title']);
		$advert->setAuthor($arrAdvert['author']);
		$advert->setContent($arrAdvert['content']);
		$advert->setUpdatedAt($arrAdvert['updated_at']);

		$manager->persist($advert);

		// Création de l'entité Image
		$image = new Image();
		$image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
		$image->setAlt('Job de rêve');

		$manager->persist($image);

		// On lie l'image à l'annonce
		$advert->setImage($image);	

		// pour la premiere candidature seulement
		if ($comptAdvert < 2) {
			// Création d'une première candidature
			$application1 = new Application();
			$application1->setAuthor('Marine');
			$application1->setContent("J'ai toutes les qualités requises.");

			// Création d'une deuxième candidature par exemple
			$application2 = new Application();
			$application2->setAuthor('Pierre');
			$application2->setContent("Je suis très motivé.");

			// On lie les candidatures à l'annonce
			$application1->setAdvert($advert);
			$application2->setAdvert($advert);

			$manager->persist($application1);
			$manager->persist($application2);
		}

		// Pour chaque compétence
		reset($listSkills);
		foreach ($listSkills as $skill) {
		  // On crée une nouvelle « relation entre 1 annonce et 1 compétence »
		  $advertSkill = new AdvertSkill();

		  // On la lie à l'annonce, qui est ici toujours la même
		  $advertSkill->setAdvert($advert);
		  // On la lie à la compétence, qui change ici dans la boucle foreach
		  $advertSkill->setSkill($skill);

		  // Arbitrairement, on dit que chaque compétence est requise au niveau 'Expert'
		  $advertSkill->setLevel('Expert');

		  // Et bien sûr, on persiste cette entité de relation, propriétaire des deux autres relations
		  $manager->persist($advertSkill);
		}

		// On boucle sur les catégories pour les lier à l'annonce
		reset($listCategories);
		foreach ($listCategories as $category) {
		  $advert->addCategory($category);
		}

		// On la persiste
		$manager->persist($advert);
    }

    // On déclenche l'enregistrement de toutes les catégories
    $manager->flush();
  }

  /**
  * Get the order of this fixture
  * @return integer
  */
  public function getOrder()
  {
    return 3;
  }

}
