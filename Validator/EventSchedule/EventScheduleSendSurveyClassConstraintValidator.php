<?php

namespace AppBundle\Validator\EventSchedule;

use AppBundle\Entity\EventScheduler\EventScheduleSendSurvey;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EventScheduleSendSurveyClassConstraintValidator extends ConstraintValidator
{
    /**
     * @param EventScheduleSendSurvey $eventSchedule
     * @param Constraint $constraint
     */
    public function validate($eventSchedule, Constraint $constraint)
    {
        $reflectionClass = new \ReflectionClass('AppBundle\Entity\EventScheduler\EventScheduleSendSurvey');
        $constants = $reflectionClass->getConstants();

        if (!in_array(strtolower($eventSchedule->getAntecedentEvent()), $constants)) {
            $this->context
                ->buildViolation('Invalid antecedent event type')
                ->setInvalidValue($eventSchedule->getAntecedentEvent())
                ->atPath('antecedent_event')
                ->addViolation();
        }

        if (true === $eventSchedule->getSendFirstReminder() &&
            null === $eventSchedule->getFirstReminderMinutesHushed()
        ) {
            $this->context
                ->buildViolation('First reminder minutes hushed is required when send first reminder is enabled')
                ->setInvalidValue($eventSchedule->getFirstReminderMinutesHushed())
                ->atPath('first_reminder_minutes_hushed')
                ->addViolation();
        }

        if (true === $eventSchedule->getSendSecondReminder() &&
            null === $eventSchedule->getSecondReminderMinutesHushed()
        ) {
            $this->context
                ->buildViolation('Second reminder minutes hushed is required when send second reminder is enabled')
                ->setInvalidValue($eventSchedule->getSecondReminderMinutesHushed())
                ->atPath('second_reminder_minutes_hushed')
                ->addViolation();
        }
    }
}
