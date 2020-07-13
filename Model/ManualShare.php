<?php

namespace AppBundle\Model;

use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

class ManualShare
{
    const BROADCASTER_TYPE_FACEBOOK         = "facebook";
    const BROADCASTER_TYPE_TWITTER          = "twitter";
    const BROADCASTER_TYPE_LINKEDIN         = "linkedin";

    /**
     * @var string $broadcasterId
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     */
    private $broadcasterId;

    /**
     * @var string $broadcasterType
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     */
    private $broadcasterType;

    /**
     * @var string $endorsementId
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     */
    private $endorsementId;

    /**
     * @var string $sharingType
     * @Serializer\Type("string")
     * @Assert\NotBlank()
     */
    private $sharingType;

    /**
     * @var string $videoUrl
     * @Serializer\Type("string")
     */
    private $videoUrl;

    /**
     * @return string
     */
    public function getBroadcasterId(): string
    {
        return $this->broadcasterId;
    }

    /**
     * @param string $broadcasterId
     */
    public function setBroadcasterId(string $broadcasterId)
    {
        $this->broadcasterId = $broadcasterId;
    }

    /**
     * @return string
     */
    public function getBroadcasterType(): string
    {
        return $this->broadcasterType;
    }

    /**
     * @param string $broadcasterType
     */
    public function setBroadcasterType(string $broadcasterType)
    {
        $this->broadcasterType = $broadcasterType;
    }

    /**
     * @return string
     */
    public function getEndorsementId(): string
    {
        return $this->endorsementId;
    }

    /**
     * @param string $endorsementId
     */
    public function setEndorsementId(string $endorsementId)
    {
        $this->endorsementId = $endorsementId;
    }

    /**
     * @return string
     */
    public function getSharingType(): string
    {
        return $this->sharingType;
    }

    /**
     * @param string $sharingType
     */
    public function setSharingType(string $sharingType)
    {
        $this->sharingType = $sharingType;
    }

    /**
     * @return string
     */
    public function getVideoUrl(): string
    {
        return $this->videoUrl;
    }

    /**
     * @param string $videoUrl
     */
    public function setVideoUrl(string $videoUrl)
    {
        $this->videoUrl = $videoUrl;
    }
}