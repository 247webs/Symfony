<?php

namespace AppBundle\Services;

use AppBundle\Entity\SocialMediaBanner;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class SocialMediaBannerService
 * @package AppBundle\Services
 */
class SocialMediaBannerService
{
    /** @var EntityManager */
    private $em;

    /** @var TokenStorage $token */
    private $token;

    /** @var MediaService $mediaService */
    private $mediaService;

    /** @var string $socialMediaBannerDirectory */
    private $socialMediaBannerDirectory;

    /**
     * SocialMediaBannerService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->token = $container->get('security.token_storage');
        $this->mediaService = $container->get('media_service');
        $this->socialMediaBannerDirectory = $container->getParameter('aws_social_media_banner_image_directory');
    }

    /**
     * @param SocialMediaBanner $socialMediaBanner
     * @return SocialMediaBanner
     */
    public function create(SocialMediaBanner $socialMediaBanner)
    {
        $socialMediaBanner->setType(strtolower($socialMediaBanner->getType()));

        $user = $this->token->getToken()->getUser();

        /** @var SocialMediaBanner $existingRecord */
        $existingRecord = $this->em->getRepository(SocialMediaBanner::class)
            ->findOneBy([
                'type' => $socialMediaBanner->getType(),
                'user' => $user
            ]);

        if ($existingRecord) {
            return $this->update($existingRecord, $socialMediaBanner);
        }

        $socialMediaBanner->setUser($user);

        $filename = $this->mediaService->putBase64EncodedImage(
            $socialMediaBanner->getBanner(),
            $this->socialMediaBannerDirectory
        );
        $socialMediaBanner->setBanner($filename);

        return $this->save($socialMediaBanner);
    }

    /**
     * @param SocialMediaBanner $id
     * @param SocialMediaBanner $socialMediaBanner
     * @return SocialMediaBanner
     */
    public function update(SocialMediaBanner $id, SocialMediaBanner $socialMediaBanner)
    {
        if ($socialMediaBanner->getBanner() !== $id->getBanner()) {
            $filename = $this->mediaService->putBase64EncodedImage(
                $socialMediaBanner->getBanner(),
                $this->socialMediaBannerDirectory
            );

            $id->setBanner($filename);
        }

        return $this->save($id);
    }

    /**
     * @param SocialMediaBanner $id
     * @return null
     */
    public function delete(SocialMediaBanner $id)
    {
        $this->em->remove($id);
        $this->em->flush();

        return null;
    }

    /**
     * @param SocialMediaBanner $socialMediaBanner
     * @return SocialMediaBanner
     */
    private function save(SocialMediaBanner $socialMediaBanner)
    {
        $this->em->persist($socialMediaBanner);
        $this->em->flush($socialMediaBanner);

        return $socialMediaBanner;
    }
}
