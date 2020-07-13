<?php

namespace AppBundle\Controller;

use AppBundle\Document\OfferResponse;
use AppBundle\Entity\Branch;
use AppBundle\Entity\Company;
use AppBundle\Entity\CompanySettings;
use AppBundle\Entity\SocialMediaBanner;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class FacebookOpengraphProfileController extends Controller
{
    /**
     * @Route(path="/profile-for-social/{type}/{slug}", name="profile")
     */
    public function profileAction(Request $request, $type, $slug)
    {
        switch (strtolower($type)) {
            case 'user':
                $data = $this->getUserData($slug);
                break;
            case 'branch':
                $data = $this->getBranchData($slug);
                break;
            case 'company':
                $data = $this->getCompanyData($slug);
                break;
            default:
                throw new NotFoundHttpException();
        }

        /** @var User $user */
        $user = $data->user;

        $data->banner = $this->getSocialMediaBanner($user);

        $data->description = $this->getDescription($request->query->get('offer'), $data->profile);

        $data->title = $this->getTitle($request->query->get('offer'), $data->profile);

        return $this->render('profileTemplate.html.twig', [ 'data' => $data ]);
    }

    /**
     * @param string $slug
     * @return \stdClass
     */
    private function getUserData(string $slug)
    {
        $data = new \stdClass;
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        /** @var User $user */
        $user = $userRepo->findOneBy(['slug' => $slug]);

        if (!$user || !$user->getProfile()) {
            throw new NotFoundHttpException();
        }

        $data->user = $user;
        $data->profile = $this->get('profile_service')
            ->serializeUserProfile($user->getProfile(), true, ['public', 'profile']);

        return $data;
    }

    /**
     * @param string $slug
     * @return \stdClass
     */
    private function getBranchData(string $slug)
    {
        $data = new \stdClass;
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        $branch = $this->getDoctrine()->getRepository(Branch::class)->findOneBy(['slug' => $slug]);

        if (!$branch || !$branch->getProfile()) {
            throw new NotFoundHttpException();
        }

        $data->user = $userRepo->getBranchAdministrator($branch);
        $data->profile = $this->get('profile_service')
            ->serializeBranchProfile($branch->getProfile(), true, ['public', 'profile']);

        return $data;
    }

    /**
     * @param string $slug
     * @return \stdClass
     */
    private function getCompanyData(string $slug)
    {
        $data = new \stdClass;
        $userRepo = $this->getDoctrine()->getRepository(User::class);

        $company = $this->getDoctrine()->getRepository(Company::class)->findOneBy(['slug' => $slug]);

        if (!$company || !$company->getProfile()) {
            throw new NotFoundHttpException();
        }

        $data->user = $userRepo->getCompanyAdministrator($company);
        $data->profile = $this->get('profile_service')
            ->serializeCompanyProfile($company->getProfile(), true, ['public', 'profile']);

        return $data;
    }

    /**
     * @param User $user
     * @return mixed|string
     */
    private function getSocialMediaBanner(User $user)
    {
        /** @var CompanySettings $companySettings
         *  Check for company settings for this user
         */
        $companySettings = $this->getCompanySettings($user);

        /** If company has settings and has opted to suppress social images,
         *  check to see if company admin has uploaded a custom banner for the
         *  broadcaster type specified.  If so, return the banner string.
         *  Otherwise, proceed.
         */
        if ($companySettings && $companySettings->getSuppressSocialImages()) {
            if ($companyBanner = $this->getCompanyBanner($companySettings, 'facebook')) {
                return $companyBanner;
            }
        }

        if ($user->getSocialMediaBanners()) {
            /** @var SocialMediaBanner $socialMediaBanner */
            foreach ($user->getSocialMediaBanners() as $socialMediaBanner) {
                if ($socialMediaBanner->getType() === 'facebook') {
                    return $this->getParameter('aws_cloudfront') . '/' .
                        $this->getParameter('aws_social_media_banner_image_directory') . '/' .
                        $socialMediaBanner->getBanner();
                }
            }
        }

        if ($user->getReseller() && null !== $user->getReseller()->getFacebookLogo()) {
            return $this->getParameter('aws_cloudfront') . '/' .
                $this->getParameter('aws_reseller_logo_image_directory') . '/' .
                $user->getReseller()->getFacebookLogo();
        }

        return $this->getParameter('facebook_banner');
    }

    /**
     * @param User $user
     * @return CompanySettings|null
     */
    private function getCompanySettings(User $user)
    {
        // Make sure that the user has a branch and a company
        if (!$user->getBranch() || !$user->getBranch()->getCompany()) {
            return null;
        }

        return $user->getBranch()->getCompany()->getSettings();
    }

    /**
     * @param CompanySettings $companySettings
     * @param string $type
     * @return bool|string
     */
    private function getCompanyBanner(CompanySettings $companySettings, string $type)
    {
        /** @var User $companyAdmin */
        $companyAdmin = $this->getDoctrine()->getRepository(User::class)
            ->getCompanyAdministrator($companySettings->getCompany());

        if (!$companyAdmin) {
            return false;
        }

        if ($companyAdmin->getSocialMediaBanners()) {
            /** @var SocialMediaBanner $socialMediaBanner */
            foreach ($companyAdmin->getSocialMediaBanners() as $socialMediaBanner) {
                if ($socialMediaBanner->getType() === $type) {
                    return $this->getParameter('aws_cloudfront') . '/' .
                        $this->getParameter('aws_social_media_banner_image_directory') . '/'.
                        $socialMediaBanner->getBanner();
                }
            }
        }

        return false;
    }

    /**
     * @param string|null $offerResponseId
     * @param array $profile
     * @return mixed|null|string
     */
    private function getDescription(string $offerResponseId = null, array $profile)
    {
        if (null !== $offerResponseId) {
            /** @var OfferResponse $offerResponse */
            $offerResponse = $this->get('doctrine.odm.mongodb.document_manager')
                ->getRepository(OfferResponse::class)
                ->find($offerResponseId);

            if ($offerResponse) {
                $comments = $this->get('offer_response_service')
                    ->getPublicOfferComment($offerResponse);

                if ($comments) {
                    return $comments;
                }
            }
        }


        return (null !== $profile['description']) ?
            $profile['description'] :
            'See reviews and learn more about ' . $profile['name'] . ' at eOffers.com';
    }

    /**
     * @param string|null $offerResponseId
     * @param array $profile
     * @return string
     */
    private function getTitle(string $offerResponseId = null, array $profile)
    {
        if (null !== $offerResponseId) {
            /** @var OfferResponse $offerResponse */
            $offerResponse = $this->get('doctrine.odm.mongodb.document_manager')
                ->getRepository(OfferResponse::class)
                ->find($offerResponseId);

            if ($offerResponse) {
                return
                    $offerResponse->getFirstName() . ' ' .
                    substr($offerResponse->getLastName(), 0, 1) . ' in ' .
                    $offerResponse->getCity() . ', ' .
                    $offerResponse->getState() . ' says:';
            }
        }

        return $profile['name'] . ' received an offer:';
    }
}
