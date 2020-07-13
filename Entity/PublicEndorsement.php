<?php

namespace AppBundle\Entity;

use JMS\Serializer\Annotation as Serializer;

/**
 * Class PublicEndorsement
 * @package AppBundle\Entity
 */
class PublicEndorsement
{
    const SOURCE_INTERNAL = 'internal';
    const SOURCE_GOOGLE_MY_BUSINESS = 'google_my_business';
    const SOURCE_FACEBOOK = 'facebook';
    const SOURCE_ZILLOW_PRO = 'zillow_pro';
    const SOURCE_ZILLOW_LENDER = 'zillow_lender';

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $id;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $author;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $author_email;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $label;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $city;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $state;

    /**
     * @var string
     * @Serializer\Type("DateTime")
     */
    private $date;

    /**
     * @var float
     * @Serializer\Type("float")
     */
    private $rating;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $recommendation_type;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $comment;

    /**
     * @var boolean
     * @Serializer\Type("boolean")
     */
    private $verified;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $reply;

    /**
     * @var \DateTime
     * @Serializer\Type("DateTime")
     */
    private $reply_date;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $source;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $video_endorsement;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $video_endorsement_thumbnail;

    /**
     * @var string
     * @Serializer\Type("string")
     */
    private $external_link;


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id)
    {
        $this->id = $id;
    }

    public function setAuthor($author)
    {
        $this->author = ucfirst($author);
        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @return string|null
     */
    public function getAuthorEmail()
    {
        return $this->author_email;
    }

    /**
     * @param string $author_email
     */
    public function setAuthorEmail(string $author_email)
    {
        $this->author_email = $author_email;
    }

    public function setLabel($label)
    {
        $this->label = ucfirst($label);
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    public function getCity()
    {
        return $this->city;
    }

    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    public function getState()
    {
        return $this->state;
    }

    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setRating($rating)
    {
        $this->rating = $rating;
        return $this;
    }

    public function getRating()
    {
        return $this->rating;
    }

    public function setRecommendationType($recommendation_type)
    {
        $this->recommendation_type = $recommendation_type;
        return $this;
    }

    public function getRecommendationType()
    {
        return $this->recommendation_type;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getVerified()
    {
        return $this->verified;
    }

    public function setVerified($verified)
    {
        $this->verified = $verified;
    }

    public function getReply()
    {
        return $this->reply;
    }

    public function setReply($reply)
    {
        $this->reply = $reply;
    }

    public function getReplyDate()
    {
        return $this->reply_date;
    }

    public function setReplyDate($reply_date)
    {
        $this->reply_date = $reply_date;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source)
    {
        $this->source = $source;
    }

    /**
     * @return string|null
     */
    public function getVideoEndorsement()
    {
        return $this->video_endorsement;
    }

    /**
     * @param string|null $video_endorsement
     */
    public function setVideoEndorsement($video_endorsement)
    {
        $this->video_endorsement = $video_endorsement;
    }

    /**
     * @return string
     */
    public function getVideoEndorsementThumbnail(): string
    {
        return $this->video_endorsement_thumbnail;
    }

    /**
     * @param string $video_endorsement_thumbnail
     */
    public function setVideoEndorsementThumbnail(string $video_endorsement_thumbnail)
    {
        $this->video_endorsement_thumbnail = $video_endorsement_thumbnail;
    }

    /**
     * @return string
     */
    public function getExternalLink()
    {
        return $this->external_link;
    }

    /**
     * @param string $external_link
     */
    public function setExternalLink($external_link)
    {
        $this->external_link = $external_link;
    }

}
