<?php

namespace AppBundle\Email;

use AppBundle\Document\OfferDispute;
use AppBundle\Document\OfferRequest;
use AppBundle\Document\OfferResponse;
use AppBundle\Entity\CompanyAccessRequest;
use AppBundle\Entity\Contact;
use AppBundle\Entity\Survey;
use AppBundle\Entity\User;
use AppBundle\Enumeration\OfferDisputeStatus;
use Swift_Transport;
use Twig_Environment;

class Mailer extends \Swift_Mailer
{
    const DEFAULT_FROM_NAME = 'eOffers';
    const DEFAULT_FROM_ADDRESS = 'request@mg.eoffers.com';

    const SUBJECT_WELCOME_EMAIL = 'Welcome to eOffers';
    const SUBJECT_PASSWORD_RESET = 'Password Reset Instructions';
    const SUBJECT_DISPUTE_NEW = 'New offer dispute';
    const SUBJECT_DISPUTE_APPROVED = 'Offer dispute approved';
    const SUBJECT_DISPUTE_REJECTED = 'Offer dispute denied';

    /** @var Twig_Environment */
    private $twig;

    /**
     * @param Swift_Transport $transport
     */
    public function __construct(Swift_Transport $transport)
    {
        parent::__construct($transport);
    }

    /**
     * @param Twig_Environment $twig
     */
    public function setTwigEnvironment(Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return array
     */
    protected function getDefaultFromAddress()
    {
        return [static::DEFAULT_FROM_ADDRESS => static::DEFAULT_FROM_NAME];
    }

    /**
     * @param Survey $survey
     * @return array
     */
    protected function getSurveySenderFromAddress(Survey $survey)
    {
        $from = $survey->getMerchantFirstName();
        if ($survey->getMerchantLastName()) {
            $from .= ' ' . $survey->getMerchantLastName();
        }

        return ['survey@mg.eoffers.com' => $from];
    }

    /**
     * @param User $user
     */
    public function sendWelcomeEmail(User $user)
    {
        $body = $this->twig->render(':Email:welcome.html.twig', [
            'user' => $user,
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject(static::SUBJECT_WELCOME_EMAIL)
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($user->getUsername()))
            ->setBody($body, 'text/html');

        $this->send($message);
    }

    /**
     * @param User $invitee
     * @param User $inviter
     * @param $link
     */
    public function sendInvitation(User $invitee, User $inviter, $link)
    {
        $body = $this->twig->render(':Email:invitation.html.twig', [
            'invitee' => $invitee,
            'inviter' => $inviter,
            'link' => $link
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject(static::SUBJECT_WELCOME_EMAIL)
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($invitee->getUsername()))
            ->setBody($body, 'text/html');

        $this->send($message);
    }

    /**
     * @param User $user
     * @param $callback
     */
    public function sendPasswordResetEmail(User $user, $callback)
    {
        $body = $this->twig->render(':Email:passwordReset.html.twig', [
            'user' => $user,
            'callback' => $callback
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject(static::SUBJECT_PASSWORD_RESET)
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($user->getUsername()))
            ->setBody($body, 'text/html');

        $this->send($message);
    }

    /**
     * @param Survey $survey
     * @param OfferRequest $offerRequest
     * @param $surveyLink
     * @return int
     */
    public function sendOfferRequestToRecipient(
        Survey $survey,
        OfferRequest $offerRequest,
        $surveyLink,
        $feedbackLink = null,
        $offersReceived = 0,
        $averageScore = 0
    ) {
        switch ($survey->getType()) {
            case Survey::SURVEY_TYPE_REVIEW_PUSH:
                $body = $this->twig->render(':Email:offer_request_review_push.html.twig', [
                    'survey' => $survey,
                    'offerRequest' => $offerRequest,
                    'surveyLink' => $surveyLink,
                    'feedbackLink' => $feedbackLink,
                    'offersReceived' => $offersReceived,
                    'averageScore' => $averageScore
                ]);
                break;
            case Survey::SURVEY_TYPE_VIDEOMONIAL:
                $body = $this->twig->render(':Email:offer_request_videomonial.html.twig', [
                    'survey' => $survey,
                    'offerRequest' => $offerRequest,
                    'surveyLink' => $surveyLink,
                    'offersReceived' => $offersReceived,
                    'averageScore' => $averageScore
                ]);
                break;
            default:
                $body = $this->twig->render(':Email:offer_request.html.twig', [
                    'survey' => $survey,
                    'offerRequest' => $offerRequest,
                    'surveyLink' => $surveyLink
                ]);
        }

        $message = new \Swift_Message;
        $message
            ->setSubject($survey->getSurveySubjectLine())
            ->setFrom($this->getSurveySenderFromAddress($survey))
            ->setReplyTo(trim($survey->getMerchantEmailAddress()))
            ->setTo(trim($offerRequest->getRecipientEmail()))
            ->setBody($body, 'text/html');

        $message->getHeaders()->addTextHeader('X-Mailgun-Variables', json_encode([
            'offer_request_id' => $offerRequest->getId(),
        ]));

        return $this->send($message);
    }

    public function sendOfferRequestReminder(
        Survey $survey,
        Contact $contact,
        $surveyLink,
        $feedbackLink = null
    ) {
        switch ($survey->getType()) {
            case Survey::SURVEY_TYPE_REVIEW_PUSH:
                $body = $this->twig->render(':Email:offer_request_review_push.html.twig', [
                    'survey' => $survey,
                    'surveyLink' => $surveyLink,
                    'feedbackLink' => $feedbackLink
                ]);
                break;
            default:
                $body = $this->twig->render(':Email:offer_request_reminder.html.twig', [
                    'survey' => $survey,
                    'contact' => $contact,
                    'surveyLink' => $surveyLink
                ]);
        }

        $message = new \Swift_Message;
        $message
            ->setSubject('Reminder: ' . $survey->getSurveySubjectLine())
            ->setFrom($this->getSurveySenderFromAddress($survey))
            ->setReplyTo(trim($survey->getMerchantEmailAddress()))
            ->setTo(trim($contact->getEmail()))
            ->setBody($body, 'text/html');

        return $this->send($message);
    }

    /**
     * @param OfferResponse $offerResponse
     * @param Survey $survey
     * @param array $responses
     * @param array $ccs
     * @return int
     */
    public function sendOfferReceiptToSurveyOwner(
        OfferResponse $offerResponse,
        Survey $survey,
        array $responses = [],
        array $ccs = []
    ) {
        $body = $this->twig->render(':Email:offerNotification.html.twig', [
            'offerResponse' => $offerResponse,
            'survey' => $survey,
            'responses' => $responses
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject('Offer Received')
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($survey->getMerchantEmailAddress()))
            ->setBody($body, 'text/html');

        if (count($ccs)) {
            $message->setCc($ccs);
        }

        return $this->send($message);
    }

    /**
     * @param OfferResponse $offerResponse
     * @param Survey $survey
     * @param array $responses
     * @param array $ccs
     * @return int
     */
    public function sendOfferVideoReceiptToSurveyOwner(
        OfferResponse $offerResponse,
        Survey $survey,
        array $ccs = []
    ) {
        $body = $this->twig->render(':Email:offerVideoNotification.html.twig', [
            'offerResponse' => $offerResponse,
            'survey' => $survey
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject('Video Offer Received')
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($survey->getMerchantEmailAddress()))
            ->setBody($body, 'text/html');

        if (count($ccs)) {
            $message->setCc($ccs);
        }

        return $this->send($message);
    }

    /**
     * @param OfferResponse $offerResponse
     * @param string $link
     * @return int
     */
    public function sendOfferVerificationRequest(OfferResponse $offerResponse, string $link)
    {
        $body = $this->twig->render(':Email:offerVerification.html.twig', [
            'offerResponse' => $offerResponse,
            'link' => $link
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject('Offer Verification Requested')
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($offerResponse->getEmail()))
            ->setBody($body, 'text/html');

        return $this->send($message);
    }

    public function sendOfferReply(OfferResponse $offerResponse, Survey $survey)
    {
        $body = $this->twig->render(':Email:offerReply.html.twig', [
            'offerResponse' => $offerResponse,
            'survey' => $survey
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject('re: Your Recent Offer')
            ->setFrom($this->getSurveySenderFromAddress($survey))
            ->setReplyTo(trim($survey->getMerchantEmailAddress()))
            ->setTo(trim($offerResponse->getEmail()))
            ->setBody($body, 'text/html');

        return $this->send($message);
    }

    public function sendOfferDisputeToAdmin($disputeLink, $to)
    {
        $body = $this->twig->render(':Email:offer_dispute.html.twig', [
            'disputeLink' => $disputeLink
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject(static::SUBJECT_DISPUTE_NEW)
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($to))
            ->setBody($body, 'text/html');

        $this->send($message);
    }

    public function sendOfferDisputeUpdate(
        OfferDispute $dispute,
        Survey $survey,
        array $to,
        array $responses = []
    ) {
        switch ($dispute->getStatus()) {
            case (OfferDisputeStatus::APPROVED):
                $template = ':Email:offer_dispute_approved.html.twig';
                $subject = static::SUBJECT_DISPUTE_APPROVED;
                break;
            case (OfferDisputeStatus::REJECTED):
                $template = ':Email:offer_dispute_rejected.html.twig';
                $subject = static::SUBJECT_DISPUTE_REJECTED;
                break;
            default:
                return;
        }

        $body = $this->twig->render($template, [
            'dispute' => $dispute,
            'offerResponse' => $dispute->getOfferResponse(),
            'survey' => $survey,
            'responses' => $responses
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject($subject)
            ->setFrom($this->getDefaultFromAddress())
            ->setTo($to)
            ->setBody($body, 'text/html');

        $this->send($message);
    }

    /**
     * @param CompanyAccessRequest $companyAccessRequest
     * @param User $recipient
     * @param string $link
     */
    public function sendAccessRequestNotice(
        CompanyAccessRequest $companyAccessRequest,
        User $recipient,
        string $link
    ) {
        $body = $this->twig->render(':Email:accessRequestNotification.html.twig', [
            'companyAccessRequest' => $companyAccessRequest,
            'recipient' => $recipient,
            'link' => $link
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject("New eOffers Team Member")
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($recipient->getUsername()))
            ->setBody($body, 'text/html');

        $this->send($message);
    }

    /**
     * @param CompanyAccessRequest $companyAccessRequest
     */
    public function sendAccessRequestGrantedNotice(CompanyAccessRequest $companyAccessRequest, string $link)
    {
        $body = $this->twig->render(':Email:accessRequestGranted.html.twig', [
            'companyAccessRequest' => $companyAccessRequest,
            'link' => $link
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject("eOffers Account Access Granted")
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($companyAccessRequest->getAccessRequestor()->getUsername()))
            ->setBody($body, 'text/html');

        $this->send($message);
    }

    /**
     * @param CompanyAccessRequest $companyAccessRequest
     */
    public function sendAccessRequestDeniedNotice(CompanyAccessRequest $companyAccessRequest, string $link)
    {
        $body = $this->twig->render(':Email:accessRequestDenied.html.twig', [
            'companyAccessRequest' => $companyAccessRequest,
            'link' => $link
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject("eOffers Account Access Declined")
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($companyAccessRequest->getAccessRequestor()->getUsername()))
            ->setBody($body, 'text/html');

        $this->send($message);
    }

    /**
     * @param User $user
     * @param string $broadcaster
     * @param string $link
     * @return int
     */
    public function sendSharingTokenExpiryNotice(User $user, string $broadcaster, string $link, string $type)
    {
        $body = $this->twig->render(':Email:sharingTokenExpired.html.twig', [
            'user' => $user,
            'broadcaster' => $broadcaster,
            'link' => $link,
            'type' => $type
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject("Re-authorize " . $broadcaster . " sharing")
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($user->getUsername()))
            ->setBody($body, 'text/html');

        return $this->send($message);
    }
}
