Doctrine Blender Bundle
=======================

This bundle in charge of the https://github.com/mapado/doctrine-blender integration into a Symfony project.

## Installation
```sh
composer require "mapado/doctrine-blender-bundle:0.*"
```

## Usage

### Entities
Taken from [doctrine mongodb documentation](http://doctrine-mongodb-odm.readthedocs.org/en/latest/cookbook/blending-orm-and-mongodb-odm.html#define-entity)

First lets define our Product document:
```php
/** @Document */
class Product
{
    /** @Id */
    private $id;

    /** @String */
    private $title;

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }
}
```

Next create the Order entity that has a $product and $productId property linking it to the Product that is stored with MongoDB:
```php
namespace Entities;

use Documents\Product;

/**
 * @Entity
 * @Table(name="orders")
 */
class Order
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Column(type="string")
     */
    private $productId;

    /**
     * @var Documents\Product
     */
    private $product;

    public function getId()
    {
        return $this->id;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setProduct(Product $product)
    {
        $this->productId = $product->getId();
        $this->product = $product;
    }

    public function getProduct()
    {
        return $this->product;
    }
}
```

### Configuration
```yaml
mapado_doctrine_blender:
    doctrine_external_associations:
        order:
            source_object_manager: 'doctrine.orm.order_entity_manager'
            classname: 'Acme\DemoBundle\Entity\Order'
            references:
                product: # this is the name of the property in the source entity
                    reference_getter: 'getProductId' # method in the source entity fetching the ref.id
                    reference_object_manager: 'doctrine_mongodb.odm.product_document_manager'
                    reference_class: 'Acme\DemoBundle\Document\Product'

                another_reference:
                    # ...

        another_source:
            # ...
```
