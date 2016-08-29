
AlpixelAdminMenuBundle
=================

[![Latest Stable Version](https://poser.pugx.org/alpixel/adminmenubundle/v/stable)](https://packagist.org/packages/alpixel/adminmenubundle) 
[![Build Status](https://scrutinizer-ci.com/g/alpixel/AlpixelAdminMenuBundle/badges/build.png?b=master)](https://scrutinizer-ci.com/g/alpixel/AlpixelAdminMenuBundle/build-status/master) 
[![Build Status](https://travis-ci.org/alpixel/AlpixelAdminMenuBundle.svg?branch=master)](https://travis-ci.org/alpixel/AlpixelAdminMenuBundle)

## Installation


* Install the package

```
composer require 'alpixel/adminmenubundle'
```

* Update AppKernel.php


```
    <?php
    // app/AppKernel.php
    
    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles[] = new Alpixel\Bundle\AdminMenuBundle\AlpixelAdminMenuBundle();

            // ...
        }

        // ...       
    }
```

* Create the menu.yml file in app/config directory, check the configuration example below

```
    mainMenu:
        IndexMenu_1: # The key is the name that will be displayed
            type: 'route'
            route: 'your_route'
            icon: 'fontawesome-icon' # The icon option is a css class which prefix with "fa fa-" 
            visibility: [ROLE_CUSTOMER_ADMIN] # You can add multiples roles defined in your security policy, the element doesn't appear in the DOM
            children: # With the children option you can add many sub items, visibility and icon can be defined for each element
                children_1: # The key is the name that will be displayed
                    type: 'route'
                    route: 'what_you_want_1'
                children_2:
                    type: 'route'
                    route: 'what_you_want_2'
                    parameters: # You can add static parameters to the route link
                        my_parameter_1: 'a value'
                        my_parameter_2: 3
```

* To display the menu use this line in your template

```
    {{ knp_menu_render('main', {depth: 2}) }}
```
