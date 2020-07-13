<?php

namespace AppBundle\Services;

use AppBundle\Model\AgileContact;
use GuzzleHttp\Client as Guzzler;
use GuzzleHttp\Exception\RequestException;

/**
 * Class AgileCrmService
 * @package AppBundle\Services
 */
class AgileCrmService
{
    /** @var string $agileUserEmail */
    private $agileUserEmail;

    /** @var string $agileApiKey */
    private $agileApiKey;

    /** @var string $agileApiEndpoint */
    private $agileApiEndpoint;

    /**
     * AgileCrmService constructor.
     * @param string $agileUserEmail
     * @param string $agileApiKey
     * @param string $agileApiEndpoint
     */
    public function __construct(string $agileUserEmail, string $agileApiKey, string $agileApiEndpoint)
    {
        $this->agileUserEmail = $agileUserEmail;
        $this->agileApiKey = $agileApiKey;
        $this->agileApiEndpoint = $agileApiEndpoint;
    }

    /**
     * @param array $params
     * @return bool|mixed
     */
    public function listContacts(array $params = [])
    {
        $url = $this->agileApiEndpoint . 'contacts?' . http_build_query($params);

        return $this->send($url, 'GET', ['accept' => 'application/json']);
    }

    /**
     * @param string $contactId
     * @return bool|mixed
     */
    public function getContactById(string $contactId)
    {
        $url = $this->agileApiEndpoint . 'contacts/' . $contactId;

        return $this->send($url, 'GET', ['accept' => 'application/json']);
    }

    /**
     * @param string $email
     * @return bool|mixed
     */
    public function getContactByEmail(string $email)
    {
        $url = $this->agileApiEndpoint . 'contacts/search/email/' . $email;

        return $this->send($url, 'GET', ['accept' => 'application/json']);
    }

    public function postContact(AgileContact $contact)
    {
        $existingContact = $this->getContactByEmail($contact->getEmail());

        if ($existingContact) {
            return $this->putContact($contact);
        }

        $url = $this->agileApiEndpoint . 'contacts';
        $data = $this->getContactProperties($contact);

        return $this->send(
            $url,
            'POST',
            ['content-type' => 'application/json', 'accept' => 'application/json'],
            $data
        );
    }

    /**
     * @param AgileContact $contact
     * @return bool|mixed
     */
    public function putContact(AgileContact $contact)
    {
        $existingContact = $this->getContactByEmail($contact->getEmail());

        if (!$existingContact) {
            return $this->postContact($contact);
        }

        $url = $this->agileApiEndpoint . 'contacts/edit-properties';
        $data = $this->getContactProperties($contact);
        $data['id'] = $existingContact->id;

        return $this->send(
            $url,
            'PUT',
            ['content-type' => 'application/json', 'accept' => 'application/json'],
            $data
        );
    }

    /**
     * @return array
     */
    private function getAuth()
    {
        return [
            $this->agileUserEmail,
            $this->agileApiKey
        ];
    }

    /**
     * @param string $url
     * @param string $method
     * @param array $headers
     * @return bool|mixed
     */
    private function send(string $url, $method = 'GET', $headers = [], $data = [])
    {
        $client = new Guzzler;

        try {
            switch ($method) {
                case 'GET':
                    $response = $client->get($url, [
                        'auth' => $this->getAuth(),
                        'headers' => $headers
                    ]);
                    break;
                case 'PUT':
                    $response = $client->put($url, [
                        'auth' => $this->getAuth(),
                        'headers' => $headers,
                        'json' => $data
                    ]);
                    break;
                case 'POST':
                    $response = $client->post($url, [
                        'auth' => $this->getAuth(),
                        'headers' => $headers,
                        'json' => $data
                    ]);
                    break;
                case 'DELETE':
                    break;
            }

            if (isset($response)) {
                return json_decode($response->getBody());
            }

            return false;
        } catch (RequestException $e) {
            return false;
        }
    }

    /**
     * @param AgileContact $contact
     * @return array
     */
    private function getContactProperties(AgileContact $contact)
    {
        return [
            "properties" => [
                $this->getContactProperty("first_name", $contact->getFirstName(), 'SYSTEM'),
                $this->getContactProperty("last_name", $contact->getLastName(), 'SYSTEM'),
                $this->getContactProperty("email", $contact->getEmail(), 'SYSTEM'),
                $this->getContactProperty("User ID", $contact->getUserId(), 'CUSTOM'),
                $this->getContactProperty("User Slug", $contact->getUserSlug(), 'CUSTOM'),
                $this->getContactProperty("User Role", $contact->getRole(), 'CUSTOM'),
                $this->getContactProperty("Join Date", $contact->getJoinDate()->format('Y-m-d h:i:s A'), 'CUSTOM'),
                $this->getContactProperty("Active", $contact->getActive(), 'CUSTOM'),

                $this->getContactProperty(
                    "phone",
                    $contact->getUserMobilePhone() ? $contact->getUserMobilePhone() : 'NONE',
                    'SYSTEM',
                    'mobile'
                ),
                $this->getContactProperty(
                    "phone",
                    $contact->getUserOfficePhone() ? $contact->getUserOfficePhone() : 'NONE',
                    'SYSTEM',
                    'work'
                ),
                $this->getContactProperty(
                    "Company Mobile Phone",
                    $contact->getCompanyMobilePhone() ? $contact->getCompanyMobilePhone() : 'NONE',
                    'CUSTOM'
                ),
                $this->getContactProperty(
                    "Company Office Phone",
                    $contact->getCompanyOfficePhone() ? $contact->getCompanyOfficePhone() : 'NONE',
                    'CUSTOM'
                ),
                $this->getContactProperty(
                    "company",
                    $contact->getCompanyName() ? $contact->getCompanyName() : 'NONE',
                    'SYSTEM'
                ),
                $this->getContactProperty(
                    "Industry",
                    $contact->getIndustry() ? $contact->getIndustry() : 'NONE',
                    'SYSTEM'
                ),
                $this->getContactProperty(
                    "Plan",
                    $contact->getPlan() ? $contact->getPlan() : 'NONE',
                    'CUSTOM'
                ),
                $this->getContactProperty(
                    "Coupon Code",
                    $contact->getCouponCode() ? $contact->getCouponCode() : 'NONE',
                    'CUSTOM'
                ),
                $this->getContactProperty(
                    "Referral Partner",
                    $contact->getReferralPartner() ? $contact->getReferralPartner() : 'NONE',
                    'CUSTOM'
                ),
                $this->getContactProperty(
                    "Stripe",
                    $contact->getStripeId() ? $contact->getStripeId() : 'NONE',
                    'CUSTOM'
                ),
                $this->getContactProperty(
                    "Branch",
                    $contact->getBranch() ? $contact->getBranch() : 'NONE',
                    'CUSTOM'
                )
            ]
        ];
    }

    /**
     * @param string $name
     * @param string $value
     * @param string $type
     * @param string $subtype
     * @return array
     */
    private function getContactProperty(string $name, string $value, string $type, string $subtype = null)
    {
        $property = [
            "name" => $name,
            'value' => $value,
            "type" => $type
        ];

        if ($subtype) {
            $property['subtype'] = $subtype;
        }

        return $property;
    }
}
