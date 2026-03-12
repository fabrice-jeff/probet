<?php

namespace App\Services;


use Doctrine\ORM\EntityManagerInterface;

class AppServices
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;
    private $lengthRandomId;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
        $this->lengthRandomId = 6;
    }

    public function getTableName($entity)
    {
        return $this->manager->getClassMetadata($entity)->getTableName();
    }

    public function getTablenamePrefix($entity)
    {
        return strtoupper(substr($this->manager->getClassMetadata($entity)->getTableName(), 0, 3));
    }

    public function checkIfEntityHasField($entity, $field)
    {
        $entityModel = $this->manager->getClassMetadata($entity);
        return $entityModel->hasField($field);
    }

    public function checkIfEntityHasAssociation($entity, $field)
    {
        $entityModel = $this->manager->getClassMetadata($entity);
        return $entityModel->hasAssociation($field);
    }

    function random_alphanumeric()
    {
        $length = $this->lengthRandomId;

        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ12345689';
        $my_string = '';

        for ($i = 0; $i < $length; $i++) {
            $pos = mt_rand(0, strlen($chars) - 1);
            $my_string .= substr($chars, $pos, 1);
        }

        return $my_string;
    }


    function combinationsTwo($array) :array {
        $result = [];
        $count = count($array);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $result[] = [$array[$i], $array[$j]];
            }
        }

        return $result;
    }

    function combinationsThree($array):array {
        $result = [];
        $count = count($array);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                for ($k = $j + 1; $k < $count; $k++) {
                    $result[] = [$array[$i], $array[$j], $array[$k]];
                }
            }
        }
        return $result;
    }
}