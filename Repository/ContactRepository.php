<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Branch;
use AppBundle\Entity\Company;
use AppBundle\Entity\Contact;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class ContactRepository extends EntityRepository
{
    /**
     * @param Contact $contact
     * @return Contact
     */
    public function save(Contact $contact)
    {
        $this->getEntityManager()->persist($contact);
        $this->getEntityManager()->flush();

        return $contact;
    }

    /**
     * @param User $user
     * @param $filter
     * @param $orderBy
     * @param $orderDirection
     * @param $active
     * @return \Doctrine\ORM\Query
     */
    public function filterByUser(User $user, $filter, $orderBy, $orderDirection, $active)
    {
        $qb = $this->createFilterQueryBuilder($filter, $orderBy, $orderDirection, $active);

        $qb->andWhere('contact.user = :user')
            ->setParameter(':user', $user);

        return $qb->getQuery();
    }

    /**
     * @param Branch $branch
     * @param $filter
     * @param $orderBy
     * @param $orderDirection
     * @param $active
     * @return \Doctrine\ORM\Query
     */
    public function filterByBranch(Branch $branch, $filter, $orderBy, $orderDirection, $active)
    {
        $qb = $this->createFilterQueryBuilder($filter, $orderBy, $orderDirection, $active);

        $qb
            ->join('contact.user', 'user')
            ->join('user.branch', 'branch')
            ->andWhere('branch = :branch')
            ->setParameter(':branch', $branch);

        return $qb->getQuery();
    }

    /**
     * @param Company $company
     * @param $filter
     * @param $orderBy
     * @param $orderDirection
     * @param $active
     * @return \Doctrine\ORM\Query
     */
    public function filterByCompany(Company $company, $filter, $orderBy, $orderDirection, $active)
    {
        $qb = $this->createFilterQueryBuilder($filter, $orderBy, $orderDirection, $active);

        $qb
            ->join('contact.user', 'user')
            ->join('user.branch', 'branch')
            ->join('branch.company', 'company')
            ->andWhere('company = :company')
            ->setParameter(':company', $company);

        return $qb->getQuery();
    }

    /**
     * @param $filter
     * @param $orderBy
     * @param $orderDirection
     * @param $active
     * @return \Doctrine\ORM\Query
     */
    public function filter($filter, $orderBy, $orderDirection, $active)
    {
        return $this->createFilterQueryBuilder($filter, $orderBy, $orderDirection, $active)->getQuery();
    }

    /**
     * @param $filter
     * @param $orderBy
     * @param $orderDirection
     * @param $active
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function createFilterQueryBuilder($filter, $orderBy, $orderDirection, $active)
    {
        $qb = $this->createQueryBuilder('contact');

        if (!empty($filter)) {
            $qb
                ->orWhere($qb->expr()->like('contact.id', ':filter'))
                ->orWhere($qb->expr()->like('contact.firstName', ':filter'))
                ->orWhere($qb->expr()->like('contact.lastName', ':filter'))
                ->orWhere($qb->expr()->like('contact.email', ':filter'))
                ->orWhere($qb->expr()->like('contact.city', ':filter'))
                ->orWhere($qb->expr()->like('contact.state', ':filter'))
                ->orWhere($qb->expr()->like('contact.phone', ':filter'))
                ->orWhere($qb->expr()->like('contact.secondaryPhone', ':filter'))
                ->orWhere($qb->expr()->like('contact.facebookImAddress', ':filter'))
                ->setParameter('filter', '%' . $filter . '%');
        }

        switch ($active) {
            case ('active'):
                $qb->andWhere('contact.active = true');
                break;
            case ('inactive'):
                $qb->andWhere('contact.active = false');
                break;
        }

        switch (strtolower($orderBy)) {
            case ('first_name'):
                $qb->orderBy('contact.firstName', $orderDirection);
                break;
            case ('last_name'):
                $qb->orderBy('contact.lastName', $orderDirection);
                break;
            case ('email'):
                $qb->orderBy('contact.email', $orderDirection);
                break;
            case ('city'):
                $qb->orderBy('contact.city', $orderDirection);
                break;
            case ('state'):
                $qb->orderBy('contact.state', $orderDirection);
                break;
            case ('phone'):
                $qb->orderBy('contact.phone', $orderDirection);
                break;
            case ('secondary_phone'):
                $qb->orderBy('contact.secondaryPhone', $orderDirection);
                break;
            case ('facebook_im_address'):
                $qb->orderBy('contact.facebookImAddress', $orderDirection);
                break;
            default:
                $qb->orderBy('contact.id', $orderDirection);
        }

        return $qb;
    }

    /**
     * @return mixed
     */
    public function getCount(
        User $user = null,
        Branch $branch = null,
        Company $company = null
    ) {
        $qb = $this->createQueryBuilder('contact')
            ->join('contact.user', 'user')
            ->join('user.branch', 'branch')
            ->join('branch.company', 'company');

        if (null !== $user) {
            $qb->where('user = :user')
                ->setParameter('user', $user);
        };

        if (null !== $branch) {
            $qb->andWhere('branch = :branch')
                ->setParameter('branch', $branch);
        }

        if (null !== $company) {
            $qb->andWhere('company = :company')
                ->setParameter('company', $company);
        }

        return $qb->select('count(contact.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
