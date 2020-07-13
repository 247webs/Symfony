<?php

namespace AppBundle\Validator\EventSchedule;

use AppBundle\Entity\EventScheduler\EventScheduleAbstract;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EventScheduleClassConstraintValidator extends ConstraintValidator
{
    /**
     * @param EventScheduleAbstract $eventSchedule
     * @param Constraint $constraint
     */
    public function validate($eventSchedule, Constraint $constraint)
    {
        $reflectionClass = new \ReflectionClass('AppBundle\Entity\EventScheduler\EventScheduleAbstract');
        $constants = $reflectionClass->getConstants();

        if (!in_array(strtolower($eventSchedule->getScheduleType()), $constants)) {
            $this->context
                ->buildViolation('Invalid schedule type')
                ->setInvalidValue($eventSchedule->getScheduleType())
                ->atPath('schedule_type')
                ->addViolation();
        }

        if (strtolower($eventSchedule->getScheduleType()) == EventScheduleAbstract::SCHEDULE_TYPE_TIME_DELAYED) {
            if (null === $eventSchedule->getMinutesDelay()) {
                $this->context
                    ->buildViolation('Minutes delay is required for time-delayed events')
                    ->setInvalidValue($eventSchedule->getMinutesDelay())
                    ->atPath('minutes_delayed')
                    ->addViolation();
            }
        }

        if (strtolower($eventSchedule->getScheduleType()) == EventScheduleAbstract::SCHEDULE_TYPE_AT_DATETIME) {
            if (null === $eventSchedule->getExecuteAtTime()) {
                $this->context
                    ->buildViolation('Execute at time is required for scheduled events')
                    ->setInvalidValue($eventSchedule->getExecuteAtTime())
                    ->atPath('execute_at_time')
                    ->addViolation();
            }

            if (null === $eventSchedule->getExecuteAtDaysOfWeek()) {
                $this->context
                    ->buildViolation('Execute at days of week is required for scheduled events')
                    ->setInvalidValue($eventSchedule->getExecuteAtDaysOfWeek())
                    ->atPath('execute_at_days_of_week')
                    ->addViolation();
            }

            if (is_array($eventSchedule->getExecuteAtDaysOfWeek()) &&
                empty($eventSchedule->getExecuteAtDaysOfWeek())
            ) {
                $this->context
                    ->buildViolation('Execute at days of week array can not be empty')
                    ->setInvalidValue($eventSchedule->getExecuteAtDaysOfWeek())
                    ->atPath('execute_at_days_of_week')
                    ->addViolation();
            }
        }
    }
}
