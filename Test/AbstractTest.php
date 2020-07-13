<?php

namespace AppBundle\Test;

use AppBundle\Utilities\EntityFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class AbstractTest extends KernelTestCase
{
    const HOST = 'api.eoffers.dev';
    const ADMIN_USERNAME = 'admin@example.org';
    const ADMIN_PASSWORD = 'qwerty';

    protected $defaultClientOptions = [
        'verify' => false,
        'debug' => false,
    ];

    protected $skipCreateAdminUser = false;

    protected function skipCreateAdminUser()
    {
        $this->skipCreateAdminUser = true;
    }

    public function setUp()
    {
        parent::setUp();
        $this->getKernel()->getContainer()->get('database_handler')->reloadTestSchema();
        $mongoDbName = $this->getContainer()->getParameter('mongo_default_database');
        $this->getKernel()->getContainer()->get('doctrine_mongodb.odm.default_connection')->dropDatabase($mongoDbName);

        if($this->skipCreateAdminUser) {
            return;
        }

        $this->createAdminUser();
    }

    protected function createAdminUser()
    {
        $userService = $this->getContainer()->get('user_service');
        $user = EntityFactory::aUser([
            'roles' => ['ROLE_ADMIN'],
            'username' => static::ADMIN_USERNAME,
            'password' => static::ADMIN_PASSWORD,
        ]);
        $userService->createUser($user, static::ADMIN_PASSWORD, true);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getKernel()->getContainer();
    }

    /**
     * @param string $env
     * @return KernelInterface
     */
    protected function getKernel($env = 'test')
    {
        if (null === static::$kernel) {
            static::$kernel = static::createKernel([
                'environment' => $env,
            ]);
        }

        if (!static::$kernel->getContainer()) {
            static::$kernel->boot();
        }

        return static::$kernel;
    }

    /**
     * @param string $route
     * @param array $params
     * @param int $absolute
     * @return string
     */
    protected function generateUrl($route, array $params = [], $absolute = UrlGeneratorInterface::ABSOLUTE_URL)
    {
        $router = $this->getContainer()->get('router');

        $context = new RequestContext('/app_test.php', 'GET', $this->getHost(), 'https');
        $router->setContext($context);

        return $router->generate($route, $params, $absolute);
    }

    /**
     * @return string
     */
    private function getHost()
    {
        return static::HOST;
    }
}