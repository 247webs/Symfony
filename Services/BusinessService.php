<?php

namespace AppBundle\Services;

use AppBundle\Entity\BranchProfile;
use AppBundle\Entity\CompanyProfile;
use AppBundle\Entity\UserProfile;
use Doctrine\ORM\EntityManager;

/**
 * Class BusinessService
 * @package AppBundle\Services
 */
class BusinessService
{
    /** @var EntityManager $em */
    private $em;

    /** @var GoogleMapService $googleMapService */
    private $googleMapService;

    /**
     * BusinessService constructor.
     * @param EntityManager $em
     * @param GoogleMapService $googleMapService
     */
    public function __construct(
        EntityManager $em,
        GoogleMapService $googleMapService
    ) {
        $this->em = $em;
        $this->googleMapService = $googleMapService;
    }

    /**
     * @param String $industry
     * @param Int $rating
     * @param String $searchText
     * @param String $latitude
     * @param String $longitude
     * @return array
     */
    public function searchBusiness(string $industry=null, int $rating=null, string $searchText=null, string $latitude=null, string $longitude=null) {

        /** Get Industry by name */
        $industryRepo = $this->em->getRepository('AppBundle:Industry');
        $industryData = $industryRepo->findOneByName($industry);

        /** Return blank result, If the given industry does not exist in our DB */
        if($industry && !$industryData)
            return [];

        $industryId = ($industryData) ? $industryData->getId() : 0;

        $companyProfileRepo = $this->em->getRepository('AppBundle:CompanyProfile');
        $branchProfileRepo = $this->em->getRepository('AppBundle:BranchProfile');
        $userProfileRepo = $this->em->getRepository('AppBundle:UserProfile');

        /** Get Business profiles based on given keywords */
        $userProfiles = $this->getPublicProfile($userProfileRepo->getProfiles($industryId, $searchText, $rating), 'user');

        $branchProfiles = $this->getPublicProfile($branchProfileRepo->getProfiles($industryId, $searchText, $rating), 'branch');

        $companyProfiles = $this->getPublicProfile($companyProfileRepo->getProfiles($industryId, $searchText, $rating), 'company');

        /** Return merged profiles */
        $profiles = array_merge($userProfiles, $branchProfiles, $companyProfiles);

        $profiles = $this->filterByLocation($profiles, $latitude, $longitude);

        return $profiles;
    }

    /**
     * @param array $profileArray
     * @param string $profileType
     * @return array
     */
    public function getPublicProfile(array $profileArray, string $profileType) {
        if(empty($profileArray)) return [];

        $result = [];
        $key = 0;
        foreach ($profileArray as $profile) {
            $result[$key]['id']                     = $profile->getId();
            $result[$key]['name']                   = $profile->getName();
            $result[$key]['type']                   = $profileType;
            $result[$key]['rating']                 = $profile->getAverageRating()*5;
            $result[$key]['scorable_offers']  = $profile->getScorableOffers();

            /** Get profile slug */
            switch ($profileType) {
                case 'company':
                    $result[$key]['slug']           = $profile->getCompany()->getSlug();
                    break;

                case 'branch':
                    $result[$key]['slug']           = $profile->getBranch()->getSlug();
                    break;

                default:
                    $result[$key]['slug']           = $profile->getUser()->getSlug();
                    break;
            }

            /** Get all the addresses */
            $result[$key]['addresses']              = [];
            if(!empty($profile->getAddresses())) {
                foreach ($profile->getAddresses() as $address) {
                    
                    $fullAddress = trim($address->getAddressLine1());
                    if($fullAddress != "" && substr($fullAddress, -2) != ",+") $fullAddress .= ",+";

                    $fullAddress .= trim($address->getAddressLine2());
                    if($fullAddress != "" && substr($fullAddress, -2) != ",+") $fullAddress .= ",+";

                    $fullAddress .= trim($address->getCity());
                    if($fullAddress != "" && substr($fullAddress, -2) != ",+") $fullAddress .= ",+";

                    $fullAddress .= trim($address->getState());
                    if($fullAddress != "" && substr($fullAddress, -2) != ",+") $fullAddress .= ",+";

                    $fullAddress .= trim($address->getZip());

                    $result[$key]['addresses'][] = str_replace(' ', '+', $fullAddress);
                }
            }

            $key++;
        }
        return $result;
    }

    /**
     * @param array $profiles
     * @param String $latitude
     * @param String $longitude
     * @return array
     */
    public function filterByLocation(array $profiles, string $latitude=null, string $longitude=null) {

        $withAddressProfiles = $withoutAddressProfiles = $profileAddresses = $profileDistancesFromOrigin = [];

        foreach ($profiles as $profile) {
            if(!empty($profile['addresses'])) {
                $withAddressProfiles[] = $profile;
                $profileAddresses = array_merge($profileAddresses, $profile['addresses']);
            } else {
                $withoutAddressProfiles[] = $profile;
            }
        }

        /** Get the distance matrix based on the given latitude and longitude */
        $distanceMatrix = $this->googleMapService->getDistanceMatrix([$latitude.",".$longitude], $profileAddresses);
        if($distanceMatrix->status === "OK") {
            $distanceMatrix = (array) $distanceMatrix;
            $elements = $distanceMatrix['rows'][0]->elements;
            foreach($elements as $element) {
                if($element->status === "OK") {
                    $profileDistancesFromOrigin[] = $element->distance->value; // in meter
                } else {
                    $profileDistancesFromOrigin[] = null;
                }
            }
        }

        $withAddressAndDistanceProfiles = $this->addDistanceFromOrigin($withAddressProfiles, $profileDistancesFromOrigin);

        return array_merge($withAddressAndDistanceProfiles, $withoutAddressProfiles);
    }

    /**
     * @param array $withAddressProfiles
     * @param array $profileDistancesFromOrigin
     * @return array $profiles
     */
    public function addDistanceFromOrigin($withAddressProfiles, $profileDistancesFromOrigin) {
        $count = 0;
        $withAddressAndDistanceProfiles = [];
        foreach ($withAddressProfiles as $key=>$profile) {
            for ($i=0; $i < count($profile['addresses']); $i++) { 
                $profile['distance'][] = $profileDistancesFromOrigin[$count+$i];
            }
            $withAddressAndDistanceProfiles[] = $profile;
            $count = $count + count($profile['addresses']);
        }
        return $withAddressAndDistanceProfiles;
    }
}