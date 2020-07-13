<?php

namespace AppBundle\Validator;

use AppBundle\Document\Answer\CommentBoxAnswer;
use AppBundle\Document\Answer\DropdownAnswer;
use AppBundle\Document\Answer\MultipleChoiceAnswer;
use AppBundle\Document\Answer\SingleTextboxAnswer;
use AppBundle\Document\Answer\StarRatingAnswer;
use AppBundle\Document\EndorsementResponseAnswer;
use AppBundle\Entity\SurveyQuestion;
use AppBundle\Repository\SurveyQuestionRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class AnswerRequiredConstraintValidator
 * @package AppBundle\Validator
 */
class AnswerRequiredConstraintValidator extends ConstraintValidator
{
    private $surveyQuestionRepository;

    /**
     * AnswerRequiredConstraintValidator constructor.
     * @param SurveyQuestionRepository $surveyQuestionRepository
     */
    public function __construct(SurveyQuestionRepository $surveyQuestionRepository)
    {
        $this->surveyQuestionRepository = $surveyQuestionRepository;
    }

    /**
     * @param EndorsementResponseAnswer     $answer
     * @param Constraint $constraint
     */
    public function validate($answer, Constraint $constraint)
    {
        /** @var SurveyQuestion $surveyQuestion */
        $surveyQuestion = $this->surveyQuestionRepository->find($answer->getQuestionId());

        if (!$surveyQuestion) {
            $this->context->buildViolation("Invalid survey question ID")
                ->setInvalidValue($answer->getQuestionId())
                ->addViolation();
        }

        if ($surveyQuestion->getIsRequired()) {
            if ($answer instanceof CommentBoxAnswer && null === $answer->getAnswer()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('answer')
                    ->addViolation();
            }

            if ($answer instanceof DropdownAnswer && null === $answer->getChoice()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('choice')
                    ->addViolation();
            }

            if ($answer instanceof MultipleChoiceAnswer && null === $answer->getSelectedChoices()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('selected_choices')
                    ->addViolation();
            }

            if ($answer instanceof SingleTextboxAnswer  && null === $answer->getAnswer()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('answer')
                    ->addViolation();
            }

            if ($answer instanceof StarRatingAnswer && null === $answer->getRating()) {
                $this->context->buildViolation($constraint->message)
                    ->atPath('rating')
                    ->addViolation();
            }
        }
    }
}
