<?php

namespace App\DataFixtures;


use App\Entity\Actor;
use App\Entity\Country;
use App\Entity\User;
use App\Utils\Constants\AppValuesConstants;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager, ): void
    {
        $countries = [
            "Afghanistan",
            "Afrique du Sud",
            "Albanie",
            "Algérie",
            "Allemagne",
            "Andorre",
            "Angola",
            "Antigua-et-Barbuda",
            "Arabie saoudite",
            "Argentine",
            "Arménie",
            "Australie",
            "Autriche",
            "Azerbaïdjan",
            "Bahamas",
            "Bahreïn",
            "Bangladesh",
            "Barbade",
            "Belgique",
            "Belize",
            "Bénin",
            "Bhoutan",
            "Biélorussie",
            "Birmanie (Myanmar)",
            "Bolivie",
            "Bosnie-Herzégovine",
            "Botswana",
            "Brésil",
            "Brunei",
            "Bulgarie",
            "Burkina Faso",
            "Burundi",
            "Cambodge",
            "Cameroun",
            "Canada",
            "Cap-Vert",
            "Chili",
            "Chine",
            "Chypre",
            "Colombie",
            "Comores",
            "Congo-Brazzaville (République du Congo)",
            "Congo-Kinshasa (République démocratique du Congo)",
            "Corée du Nord",
            "Corée du Sud",
            "Costa Rica",
            "Côte d'Ivoire",
            "Croatie",
            "Cuba",
            "Danemark",
            "Djibouti",
            "Dominique",
            "Égypte",
            "Émirats arabes unis",
            "Équateur",
            "Érythrée",
            "Espagne",
            "Eswatini (Swaziland)",
            "Estonie",
            "États-Unis",
            "Éthiopie",
            "Fidji",
            "Finlande",
            "France",
            "Gabon",
            "Gambie",
            "Géorgie",
            "Ghana",
            "Grèce",
            "Grenade",
            "Guatemala",
            "Guinée",
            "Guinée-Bissau",
            "Guinée équatoriale",
            "Guyana",
            "Haïti",
            "Honduras",
            "Hongrie",
            "Îles Cook",
            "Îles Marshall",
            "Inde",
            "Indonésie",
            "Irak",
            "Iran",
            "Irlande",
            "Islande",
            "Israël",
            "Italie",
            "Jamaïque",
            "Japon",
            "Jordanie",
            "Kazakhstan",
            "Kenya",
            "Kirghizistan",
            "Kiribati",
            "Koweït",
            "Laos",
            "Lesotho",
            "Lettonie",
            "Liban",
            "Liberia",
            "Libye",
            "Liechtenstein",
            "Lituanie",
            "Luxembourg",
            "Macédoine du Nord",
            "Madagascar",
            "Malaisie",
            "Malawi",
            "Maldives",
            "Mali",
            "Malte",
            "Maroc",
            "Maurice",
            "Mauritanie",
            "Mexique",
            "Micronésie",
            "Moldavie",
            "Monaco",
            "Mongolie",
            "Monténégro",
            "Mozambique",
            "Namibie",
            "Nauru",
            "Népal",
            "Nicaragua",
            "Niger",
            "Nigeria",
            "Norvège",
            "Nouvelle-Zélande",
            "Oman",
            "Ouganda",
            "Ouzbékistan",
            "Pakistan",
            "Palaos",
            "Panama",
            "Papouasie-Nouvelle-Guinée",
            "Paraguay",
            "Pays-Bas",
            "Pérou",
            "Philippines",
            "Pologne",
            "Portugal",
            "Qatar",
            "République centrafricaine",
            "République dominicaine",
            "Roumanie",
            "Royaume-Uni",
            "Russie",
            "Rwanda",
            "Saint-Christophe-et-Niévès",
            "Sainte-Lucie",
            "Saint-Vincent-et-les-Grenadines",
            "Salomon (Îles)",
            "Salvador",
            "Samoa",
            "Sao Tomé-et-Principe",
            "Sénégal",
            "Serbie",
            "Seychelles",
            "Sierra Leone",
            "Singapour",
            "Slovaquie",
            "Slovénie",
            "Somalie",
            "Soudan",
            "Soudan du Sud",
            "Sri Lanka",
            "Suède",
            "Suisse",
            "Suriname",
            "Syrie",
            "Tadjikistan",
            "Tanzanie",
            "Tchad",
            "Tchéquie",
            "Thaïlande",
            "Timor oriental",
            "Togo",
            "Tonga",
            "Trinité-et-Tobago",
            "Tunisie",
            "Turkménistan",
            "Turquie",
            "Tuvalu",
            "Ukraine",
            "Uruguay",
            "Vanuatu",
            "Vatican",
            "Venezuela",
            "Viêt Nam",
            "Yémen",
            "Zambie",
            "Zimbabwe",
            "Palestine"
        ];
        foreach ($countries as $value) {
            $country = new Country();

            $country->setName($value);
            $manager->persist($country);
            if($value == "Bénin"){
                /*
                 * Création du compte utilisateur de l'administracteur
                 * */
                $email = "superadmin@provbet.com";
                $password = "superadmin";
                $lastName =  "SUPER";
                $firstName =  "ADMIN";
                $user = new User();
                $password = $this->passwordHasher->hashPassword($user,$password);
                $user->setEmail($email)
                    ->setActive(true)
                    ->setRoles([AppValuesConstants::ROLE_SUPER_ADMIN])
                    ->setPassword($password);
                $manager->persist($user);

                $identifier = "SUAD0000";
                $actor = new Actor();
                $actor->setEmail($email)
                    ->setLastName($lastName)
                    ->setFirstName($firstName)
                    ->setCountry($country)
                    ->setMainWallet(0)
                    ->setReattachWallet(0)
                    ->setUser($user)
                    ->setIdentifier($identifier);
                $manager->persist($actor);
            }
        }
        $manager->flush();
    }
}
