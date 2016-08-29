<?php

namespace Alpixel\Bundle\AdminMenuBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\MenuItem;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Yaml\Parser;

/**
 * @author Benjamin HUBERT <benjamin@alpixel.fr>
 */
class MenuBuilder
{
    use \Symfony\Component\DependencyInjection\ContainerAwareTrait;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    protected $router;
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    protected $factory;
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser
     */
    protected $parser;
    /**
     * @var mixed|null
     */
    protected $user;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * MenuBuilder constructor.
     * @param \Knp\Menu\FactoryInterface $factory
     * @param \Symfony\Bundle\FrameworkBundle\Routing\Router $router
     * @param \Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser $parser
     * @param \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage $tokenStorage
     * @param \Symfony\Component\Security\Core\Authorization\AuthorizationChecker $authorizationChecker
     */
    public function __construct(
        FactoryInterface $factory,
        Router $router,
        ControllerNameParser $parser,
        TokenStorage $tokenStorage,
        AuthorizationChecker $authorizationChecker
    ) {
        $this->factory = $factory;
        $this->router = $router;
        $this->parser = $parser;
        $this->user = ($tokenStorage->getToken() !== null) ? $tokenStorage->getToken()->getUser() : null;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param $kernelRootDir
     * @return \Knp\Menu\ItemInterface
     */
    public function createMainMenu(RequestStack $requestStack, $kernelRootDir)
    {
        $routes = $this->router->getRouteCollection();

        foreach ($routes as $route) {
            $this->convertController($route);
        }

        $request = $requestStack->getCurrentRequest();

        $menu = $this->factory->createItem(
            'root',
            [
                'childrenAttributes' => [
                    'class' => 'sidebar-menu',
                ],
            ]
        );

        $parser = new Parser();
        $items = $parser->parse(file_get_contents($kernelRootDir.'/config/menu.yml'));
        $currentUri = $request->getRequestUri();

        foreach ($items['mainMenu'] as $label => $item) {
            $this->addItem($currentUri, $menu, $label, $item);
        }

        return $menu;
    }

    /**
     * @param \Symfony\Component\Routing\Route $route
     */
    protected function convertController(\Symfony\Component\Routing\Route $route)
    {
        if ($route->hasDefault('_controller')) {
            try {
                $route->setDefault('_controller', $this->parser->build($route->getDefault('_controller')));
            } catch (\InvalidArgumentException $e) {
            }
        }
    }

    /**
     * @param $currentUri
     * @param $menu
     * @param $label
     * @param $item
     * @param null $parent
     * @return mixed
     */
    protected function addItem($currentUri, &$menu, $label, $item, $parent = null)
    {

        if (isset($item['visibility'])) {
            if (isset($item['visibility']['exclude'])) {
                $permission = $this->authorizationChecker->isGranted($item['visibility']['exclude'], $this->user);
                if ($permission === true) {
                    return;
                }
            } else {
                $permission = $this->authorizationChecker->isGranted($item['visibility'], $this->user);
                if ($permission === false) {
                    return;
                }
            }
        }

        if (isset($item['type']) && isset($item['route']) && $item['type'] == 'route') {
            $params = [
                'route' => $item['route'],
            ];
            if (isset($item['parameters'])) {
                $params['routeParameters'] = $item['parameters'];
            }
        } else {
            $params = [];
        }

        if ($parent !== null) {
            $menuItem = $parent->addChild($label, $params);
        } else {
            $menuItem = $menu->addChild($label, $params);
        }

        if (isset($item['badge']) && isset($this->container)) {
            $badge = $this->container->get($item['badge']);
            $menuItem->setAttribute('badge', $badge->getCount());
        }

        if (!isset($item['route'])) {
            $menuItem->setUri('#');
        }

        if (!empty($item['icon'])) {
            $menuItem->setAttribute('icon', $item['icon']);
        }

        if (isset($item['children'])) {
            $menuItem->setAttribute('class', 'treeview');
            foreach ($item['children'] as $childLabel => $childItem) {
                $child = $this->addItem($currentUri, $menu, $childLabel, $childItem, $menuItem);
                if ($child !== null && $currentUri == $child->getUri()) {
                    $this->setParentActive($child->getParent());
                }
            }
        }

        return $menuItem;
    }

    /**
     * @param \Knp\Menu\MenuItem $item
     */
    public function setParentActive(MenuItem $item)
    {
        $cssClass = $item->getAttribute('class');
        if (empty($cssClass)) {
            $cssClass = '';
        }

        $item->setAttribute('class', $cssClass.' active');
        if ($item->getParent() !== null) {
            $this->setParentActive($item->getParent());
        }
    }
}
