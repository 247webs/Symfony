<?php

namespace AppBundle\Test;

use AppBundle\Entity\SurveyQuestion;
use Faker\Factory;

class FixtureFactory
{
    const PASSWORD = 'Apples1!';

    /**
     * @return array
     */
    public static function aUser()
    {
        $faker = Factory::create();

        return [
            'username' => $faker->safeEmail,
            'plain_password' => static::PASSWORD,
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
        ];
    }

    /**
     * @return array
     */
    public static function aCompany()
    {
        $faker = Factory::create();

        return [
            'name' => $faker->company,
            'address_1' => $faker->address,
            'address_2' => $faker->address,
            'city' => $faker->city,
            'state' => $faker->stateAbbr,
            'zip' => $faker->postcode,
            'payee_level' => 3,
        ];
    }

    /**
     * @param $userId
     * @param array $surveyQuestions
     * @return array
     */
    public static function aSurvey($userId, array $surveyQuestions = [])
    {
        $faker = Factory::create();

        return [
            'user' => ['id' => $userId],
            'survey_name' => $faker->sentence,
            'survey_subject_line' => $faker->sentence,
            'survey_greeting' => $faker->sentence,
            'survey_message' => $faker->sentence,
            'survey_sign_off' => $faker->sentence,
            'merchant_first_name' => $faker->firstName,
            'merchant_email_address' => $faker->safeEmail,
            'survey_questions' => $surveyQuestions,
        ];
    }

    /**
     * @return array
     */
    public static function aSurveyQuestion()
    {
        $faker = Factory::create();

        return [
            'type' => SurveyQuestion::TYPE_COMMENT_BOX,
            'question' => $faker->sentence,
            'prompt' => $faker->sentence,
            'hint' => $faker->sentence,
            'is_required' => $faker->boolean,
            'position' => $faker->numberBetween(1,5),
        ];
    }

    /**
     * @param int $surveyId
     * @return array
     */
    public static function anOfferRequest($surveyId)
    {
        $faker = Factory::create();

        return [
            'survey_id' => $surveyId,
            'recipient_email' => $faker->freeEmail,
            'recipient_first_name' => $faker->firstName,
            'recipient_last_name' => $faker->lastName,
        ];
    }

    /**
     * @param string $offerRequestId
     * @return array
     */
    public static function anOfferResponse(string $offerRequestId)
    {
        $faker = Factory::create();

        return [
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'email' => $faker->freeEmail,
            'phone' => $faker->phoneNumber,
            'city' => $faker->city,
            'state' => $faker->state,
            'company_name' => $faker->company,
            'title' => $faker->title,
            'can_share' => $faker->boolean,
            'offer_request' => ['id' => $offerRequestId],
        ];
    }

    /**
     * @param $userId
     * @return array
     */
    public static function aContact($userId)
    {
        $faker = Factory::create();

        return [
            'user' => ['id' => $userId],
            'first_name' => $faker->firstName,
            'last_name' => $faker->lastName,
            'email' => $faker->freeEmail,
            'phone' => substr($faker->phoneNumber, 0, 20),
            'secondary_phone' => substr($faker->phoneNumber, 0, 20),
        ];
    }
}