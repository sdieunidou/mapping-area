<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Author
 *
 * @ORM\Table(name="author")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AuthorRepository")
 */
class Author
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="slug", type="string", length=255)
     */
    private $slug;

    /**
     * @var integer
     *
     * @Gedmo\Slug(fields={"name"})
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $userId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="register_at", type="datetime")
     */
    private $registerAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="count_messages", type="integer")
     */
    private $countMessages;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Author
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return Author
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set registerAt
     *
     * @param \DateTime $registerAt
     * @return Author
     */
    public function setRegisterAt($registerAt)
    {
        $this->registerAt = $registerAt;

        return $this;
    }

    /**
     * Get registerAt
     *
     * @return \DateTime 
     */
    public function getRegisterAt()
    {
        return $this->registerAt;
    }

    /**
     * Set countMessages
     *
     * @param integer $countMessages
     * @return Author
     */
    public function setCountMessages($countMessages)
    {
        $this->countMessages = $countMessages;

        return $this;
    }

    /**
     * Get countMessages
     *
     * @return integer 
     */
    public function getCountMessages()
    {
        return $this->countMessages;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return Author
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }
}
