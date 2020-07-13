<?php

namespace AppBundle\Email;

use AppBundle\Document\EndorsementDispute;
use AppBundle\Document\EndorsementRequest;
use AppBundle\Document\EndorsementResponse;
use AppBundle\Entity\CompanyAccessRequest;
use AppBundle\Entity\Contact;
use AppBundle\Entity\Survey;
use AppBundle\Entity\User;
use AppBundle\Enumeration\EndorsementDisputeStatus;
use Swift_Transport;
use Twig_Environment;

class Mailer extends \Swift_Mailer
{
    const DEFAULT_FROM_NAME = 'eEndorsements';
    const DEFAULT_FROM_ADDRESS = 'request@mg.eendorsements.com';

    const SUBJECT_WELCOME_EMAIL = 'Welcome to eEndorsements';
    const SUBJECT_PASSWORD_RESET = 'Password Reset Instructions';
    const SUBJECT_DISPUTE_NEW = 'New endorsement dispute';
    const SUBJECT_DISPUTE_APPROVED = 'Endorsement dispute approved';
    const SUBJECT_DISPUTE_REJECTED = 'Endorsement dispute denied';

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

        return ['survey@mg.eendorsements.com' => $from];
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
     * @param EndorsementRequest $endorsementRequest
     * @param $surveyLink
     * @return int
     */
    public function sendEndorsementRequestToRecipient(
        Survey $survey,
        EndorsementRequest $endorsementRequest,
        $surveyLink,
        $feedbackLink = null,
        $endorsementsReceived = 0,
        $averageScore = 0
    ) {
        switch ($survey->getType()) {
            case Survey::SURVEY_TYPE_REVIEW_PUSH:
                $body = $this->twig->render(':Email:endorsement_request_review_push.html.twig', [
                    'survey' => $survey,
                    'endorsementRequest' => $endorsementRequest,
                    'surveyLink' => $surveyLink,
                    'feedbackLink' => $feedbackLink,
                    'endorsementsReceived' => $endorsementsReceived,
                    'averageScore' => $averageScore
                ]);
                break;
            case Survey::SURVEY_TYPE_VIDEOMONIAL:
                $body = $this->twig->render(':Email:endorsement_request_videomonial.html.twig', [
                    'survey' => $survey,
                    'endorsementRequest' => $endorsementRequest,
                    'surveyLink' => $surveyLink,
                    'endorsementsReceived' => $endorsementsReceived,
                    'averageScore' => $averageScore
                ]);
                break;
            default:
                $body = $this->twig->render(':Email:endorsement_request.html.twig', [
                    'survey' => $survey,
                    'endorsementRequest' => $endorsementRequest,
                    'surveyLink' => $surveyLink
                ]);
        }

        $message = new \Swift_Message;
        $message
            ->setSubject($survey->getSurveySubjectLine())
            ->setFrom($this->getSurveySenderFromAddress($survey))
            ->setReplyTo(trim($survey->getMerchantEmailAddress()))
            ->setTo(trim($endorsementRequest->getRecipientEmail()))
            ->setBody($body, 'text/html');

        $message->getHeaders()->addTextHeader('X-Mailgun-Variables', json_encode([
            'endorsement_request_id' => $endorsementRequest->getId(),
        ]));

        return $this->send($message);
    }

    public function sendEndorsementRequestReminder(
        Survey $survey,
        Contact $contact,
        $surveyLink,
        $feedbackLink = null
    ) {
        switch ($survey->getType()) {
            case Survey::SURVEY_TYPE_REVIEW_PUSH:
                $body = $this->twig->render(':Email:endorsement_request_review_push.html.twig', [
                    'survey' => $survey,
                    'surveyLink' => $surveyLink,
                    'feedbackLink' => $feedbackLink
                ]);
                break;
            default:
                $body = $this->twig->render(':Email:endorsement_request_reminder.html.twig', [
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
     * @param EndorsementResponse $endorsementResponse
     * @param Survey $survey
     * @param array $responses
     * @param array $ccs
     * @return int
     */
    public function sendEndorsementReceiptToSurveyOwner(
        EndorsementResponse $endorsementResponse,
        Survey $survey,
        array $responses = [],
        array $ccs = []
    ) {
        $body = $this->twig->render(':Email:endorsementNotification.html.twig', [
            'endorsementResponse' => $endorsementResponse,
            'survey' => $survey,
            'responses' => $responses
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject('Endorsement Received')
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($survey->getMerchantEmailAddress()))
            ->setBody($body, 'text/html');

        if (count($ccs)) {
            $message->setCc($ccs);
        }

        return $this->send($message);
    }

    /**
     * @param EndorsementResponse $endorsementResponse
     * @param Survey $survey
     * @param array $responses
     * @param array $ccs
     * @return int
     */
    public function sendEndorsementVideoReceiptToSurveyOwner(
        EndorsementResponse $endorsementResponse,
        Survey $survey,
        array $ccs = []
    ) {
        $body = $this->twig->render(':Email:endorsementVideoNotification.html.twig', [
            'endorsementResponse' => $endorsementResponse,
            'survey' => $survey
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject('Video Endorsement Received')
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($survey->getMerchantEmailAddress()))
            ->setBody($body, 'text/html');

        if (count($ccs)) {
            $message->setCc($ccs);
        }

        return $this->send($message);
    }

    /**
     * @param EndorsementResponse $endorsementResponse
     * @param string $link
     * @return int
     */
    public function sendEndorsementVerificationRequest(EndorsementResponse $endorsementResponse, string $link)
    {
        $body = $this->twig->render(':Email:endorsementVerification.html.twig', [
            'endorsementResponse' => $endorsementResponse,
            'link' => $link
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject('Endorsement Verification Requested')
            ->setFrom($this->getDefaultFromAddress())
            ->setTo(trim($endorsementResponse->getEmail()))
            ->setBody($body, 'text/html');

        return $this->send($message);
    }

    public function sendEndorsementReply(EndorsementResponse $endorsementResponse, Survey $survey)
    {
        $body = $this->twig->render(':Email:endorsementReply.html.twig', [
            'endorsementResponse' => $endorsementResponse,
            'survey' => $survey
        ]);

        $message = new \Swift_Message;
        $message
            ->setSubject('re: Your Recent Endorsement')
            ->setFrom($this->getSurveySenderFromAddress($survey))
            ->setReplyTo(trim($survey->getMerchantEmailAddress()))
            ->setTo(trim($endorsementResponse->getEmail()))
            ->setBody($body, 'text/html');

        return $this->send($message);
    }

    public function sendEndorsementDisputeToAdmin($disputeLink, $to)
    {
        $body = $this->twig->render(':Email:endorsement_dispute.html.twig', [
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

    public function sendEndorsementDisputeUpdate(
        EndorsementDispute $dispute,
        Survey $survey,
        array $to,
        array $responses = []
    ) {
        switch ($dispute->getStatus()) {
            case (EndorsementDisputeStatus::APPROVED):
                $template = ':Email:endorsement_dispute_approved.html.twig';
                $subject = static::SUBJECT_DISPUTE_APPROVED;
                break;
            case (EndorsementDisputeStatus::REJECTED):
                $template = ':Email:endorsement_dispute_rejected.html.twig';
                $subject = static::SUBJECT_DISPUTE_REJECTED;
                break;
            default:
                return;
        }

        $body = $this->twig->render($template, [
            'dispute' => $dispute,
            'endorsementResponse' => $dispute->getEndorsementResponse(),
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
            ->setSubject("New eEndorsements Team Member")
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
            ->setSubject("eEndorsements Account Access Granted")
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
            ->setSubject("eEndorsements Account Access Declined")
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
