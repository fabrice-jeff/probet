<?php

namespace App\Services;

use App\Entity\Fichier;
use App\Entity\TempFicher;
use App\Entity\TypeType;
use App\Repository\TempFicherRepository;
use App\Repository\TypeTypeRepository;
use App\Utils\Constants\FixedValuesConstants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

class FichierServices
{
    private TempFicherRepository $tempFicherRepository;
    private RequestStack $requestStack;
    private EntityManagerInterface $manager;
    private TypeTypeRepository $typeTypeRepository;

    public function __construct(TempFicherRepository $tempFicherRepository, RequestStack $requestStack, EntityManagerInterface $manager, TypeTypeRepository $typeTypeRepository)
    {
        $this->tempFicherRepository = $tempFicherRepository;
        $this->requestStack = $requestStack;
        $this->manager = $manager;
        $this->typeTypeRepository = $typeTypeRepository;
    }

    public function uploadFichier($file, $directory, $typeFichier = null){
        $codeFichier = $this->generateCodeFichier();

        $libelle = md5(uniqid());

        $this->saveTempFile($file, $codeFichier, $libelle, $typeFichier);

        $this->moveToPermanentDirectory($codeFichier, $directory);

        return $codeFichier;
    }

    //Generate code fichier 8 digits
    public function generateCodeFichier()
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ12345689';
        $my_string = '';

        for ($i = 0; $i < 8; $i++) {
            $pos = mt_rand(0, strlen($chars) - 1);
            $my_string .= substr($chars, $pos, 1);
        }

        return $my_string;
    }

    public function uploadTempFile(mixed $file, string $target)
    {
        $fileName = md5(uniqid()) . '.' . $file->getClientOriginalExtension();

        $file->move($target, $fileName);

        return $fileName;
    }

    public function createFichier($file, $codeFichier, $directory, $libelle, $typeFichier, $parent = null){
        $request = $this->requestStack->getCurrentRequest();

        $tempFile = $this->saveTempFile($file, $codeFichier, $libelle, $typeFichier);

        $url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/' . $directory . $tempFile->getFileName();

        $fichier = (new Fichier())
            ->setLibelle($tempFile->getLibelle())
            ->setPath($url)
            ->setCodeFichier($codeFichier)
            ->setExtension($tempFile->getExtension())
            ->setTypeFichier($tempFile->getTypeFichier())
            ->setTaille($tempFile->getTaille());

        if ($parent){
            $fichier->setParent($parent);
        }

        $this->manager->persist($fichier);

        $this->tempFicherRepository->remove($tempFile);

        $fileInsert = new File($tempFile->getPath());

        $fileInsert->move($directory, $tempFile->getFileName());

        return $fichier;
    }

    public function moveToPermanentDirectory($codeFichier, $directory)
    {
        $request = $this->requestStack->getCurrentRequest();

        $tempFiles = $this->getTempFiles($codeFichier);

        foreach ($tempFiles as $tempFile) {

            $url = $request->getScheme() . '://' . $request->getHttpHost() . $request->getBasePath() . '/' . $directory . $tempFile->getFileName();

            $fichier = (new Fichier())
                ->setLibelle($tempFile->getLibelle())
                ->setPath($url)
                ->setCodeFichier($codeFichier)
                ->setExtension($tempFile->getExtension())
                ->setTypeFichier($tempFile->getTypeFichier())
                ->setTaille($tempFile->getTaille());

            $this->manager->persist($fichier);

            $this->tempFicherRepository->remove($tempFile);

            $fileInsert = new File($tempFile->getPath());

            $fileInsert->move($directory, $tempFile->getFileName());
        }

    }

    private function getTempFiles($codeFichier)
    {
        return $this->tempFicherRepository->findBy(['codeFichier' => $codeFichier]);
    }

    public function getLinkFromCodeFichier($codeFichier)
    {
        $fichier = $this->manager->getRepository(Fichier::class)->findOneBy(['codeFichier' => $codeFichier]);

        return $fichier?->getPath();
    }


    public function getLinkFromCode($code)
    {
        $fichier = $this->manager->getRepository(Fichier::class)->findOneBy(['code' => $code]);

        return $fichier?->getPath();
    }

    /**
     * @param mixed $file
     * @param string $codeFichier
     * @param mixed $libelle
     * @param \App\Entity\TypeType|null $typeFichier
     */
    public function saveTempFile(mixed $file, string $codeFichier, mixed $libelle, TypeType $typeFichier = null)
    {
        $taille = $file->getSize();

        $extension = $file->guessExtension();

        $fileName = $this->uploadTempFile($file, 'uploads/temp');

        $tempFichier = (new TempFicher())
            ->setCodeFichier($codeFichier)
            ->setLibelle($libelle)
            ->setTaille($taille)
            ->setExtension($extension)
            ->setTypeFichier($typeFichier)
            ->setFileName($fileName)
            ->setPath('uploads/temp/' . $fileName);

        $this->manager->persist($tempFichier);

        $this->manager->flush();

        return $tempFichier;
    }
}