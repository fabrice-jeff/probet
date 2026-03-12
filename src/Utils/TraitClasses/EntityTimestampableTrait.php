<?php
/**
 * Created by PhpStorm.
 * User: LANGANFIN Rogelio
 * Date: 02/01/2020
 * Time: 09:31
 */

namespace App\Utils\TraitClasses;


use Doctrine\ORM\Mapping as ORM;

trait EntityTimestampableTrait
{
    #[ORM\Column(type: 'datetime', nullable: true)]
    #[\Symfony\Component\Serializer\Attribute\Groups('created_at')]
    private $createdAt;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[\Symfony\Component\Serializer\Attribute\Groups('updated_at')]
    private $updatedAt;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private $deleted;

    public function __construct()
    {
        $this->updatedAt = new \DateTime();
        $this->createdAt = new \DateTime();
        $this->deleted = false;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get deleted
     *
     * @return boolean
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set deleted
     *
     * @param boolean $deleted
     *
     *
     * @return self
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * @return bool
     */
    public function isUpdatedAt()
    {
        return !!$this->updatedAt;
    }
    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param    \DateTime $updatedAt
     */
    public function setUpdatedAt( $updatedAt)
    {
        $this->updatedAt = $updatedAt;
    }
}