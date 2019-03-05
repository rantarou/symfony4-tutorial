<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LikeNotificationRepository")
 */
class LikeNotification extends Notification
{
    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MicroPost")
     */
    private $microPost;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $likedBy;

    public function getMicroPost(): ?Micropost
    {
        return $this->microPost;
    }

    public function setMicroPost(?Micropost $microPost): self
    {
        $this->microPost = $microPost;

        return $this;
    }

    public function getLikedBy(): ?User
    {
        return $this->likedBy;
    }

    public function setLikedBy(?User $likedBy): self
    {
        $this->likedBy = $likedBy;

        return $this;
    }


}
