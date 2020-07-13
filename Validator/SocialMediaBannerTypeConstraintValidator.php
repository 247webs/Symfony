<?php

namespace AppBundle\Validator;

use AppBundle\Entity\SocialMediaBanner;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class SocialMediaBannerTypeConstraintValidator
 * @package AppBundle\Validator
 */
class SocialMediaBannerTypeConstraintValidator extends ConstraintValidator
{
    /**
     * @param string $type
     * @param Constraint $constraint
     */
    public function validate($type, Constraint $constraint)
    {
        if (strtolower($type) !== SocialMediaBanner::TYPE_FACEBOOK &&
            strtolower($type) !== SocialMediaBanner::TYPE_TWITTER &&
            strtolower($type) !== SocialMediaBanner::TYPE_LINKEDIN
        ) {
            $this->context->buildViolation("Invalid social media banner type")
                ->setInvalidValue($type)
                ->addViolation();
        }
    }
}
