<?php

namespace AppBundle\Validator;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class SurveyExistsValidator extends ConstraintValidator
{
    private $em;
    
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function validate($value, Constraint $constraint)
    {
        $repo = $this->em->getRepository('AppBundle:Survey');
        $survey = $repo->find($value);
        
        if (!$survey) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}
