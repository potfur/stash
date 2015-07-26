# Stash - MongoDB ODM

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/potfur/stash/badges/quality-score.png?b=dev)](https://scrutinizer-ci.com/g/potfur/stash/?branch=dev)
[![Code Coverage](https://scrutinizer-ci.com/g/potfur/stash/badges/coverage.png?b=dev)](https://scrutinizer-ci.com/g/potfur/stash/?branch=dev)
[![Build Status](https://scrutinizer-ci.com/g/potfur/stash/badges/build.png?b=dev)](https://scrutinizer-ci.com/g/potfur/stash/build-status/dev)
[![License](https://poser.pugx.org/potfur/stash/license.svg)](https://packagist.org/packages/potfur/stash)

**Stash** is an object-document mapper for MongoDB written in PHP.
It adds a fully transparent persistence layer while still preserving MongoDB's ease of use and way of handling data.

This means that MongoDB can be used almost in the exact same way as it would be used with arrays.
The small, but important, difference here is that instead of returning plain arrays, **Stash** will return objects (entities). And of course, **Stash** not only returns entities, but it also stores them.

## Future Roadmap

 - references - **done**
 - aggregation can return mapped objects - **done**
 - store datetime as UTC - **done**
 - ~~split document converter into separate read/write converters~~ converter uses lazy conversion **done**
 - event dispatcher
 
## Example

Model definitions:

```php
$models = new \Stash\ModelCollection();
$models->register(
    new \Stash\Model\Model(
        '\Order',
        [
            new \Stash\Converter\Type\Id(),
            new \Stash\Converter\Type\Document('customer'),
            new \Stash\Converter\Type\ArrayOf('items', Fields::TYPE_DOCUMENT)
        ]
    ),
    'order'
);

$models->register(
    new \Stash\Model\
        '\OrderItem',
        [
            new \Stash\Converter\Type\Scalar('name', Fields::TYPE_STRING),
            new \Stash\Converter\Type\Scalar('amount', Fields::TYPE_INTEGER),
            new \Stash\Converter\Type\Scalar('cost', Fields::TYPE_INTEGER)
        ]
    )
);

$models->register(
    new \Stash\Model\
        '\Voucher',
        [
            new \Stash\Converter\Type\Scalar('name', Fields::TYPE_STRING),
            new \Stash\Converter\Type\Scalar('cost', Fields::TYPE_INTEGER)
        ]
    )
);

$models->register(
    new \Stash\Model\
        '\Customer',
        [
            new \Stash\Converter\Type\Scalar('name', Fields::TYPE_STRING),
            new \Stash\Converter\Type\Document('address')
        ]
    )
);

$models->register(
    new \Stash\Model\
        '\CustomerAddress',
        [
            new \Stash\Converter\Type\Scalar('address', Fields::TYPE_STRING),
            new \Stash\Converter\Type\Scalar('city', Fields::TYPE_STRING),
            new \Stash\Converter\Type\Scalar('zip', Fields::TYPE_STRING)
        ]
    )
);
```

Database connection:

```php
$types = [
    new \Stash\Converter\Type\IdType(),
    new \Stash\Converter\Type\BooleanType(),
    new \Stash\Converter\Type\IntegerType(),
    new \Stash\Converter\Type\DecimalType(),
    new \Stash\Converter\Type\StringType(),
    new \Stash\Converter\Type\DateType(),
    new \Stash\Converter\Type\ArrayType(),
    new \Stash\Converter\Type\DocumentType()
];

$proxyAdapter = new \Stash\ProxyAdapter();
$converter = new \Stash\Converter\Converter($types);
$referencer = new \Stash\ReferenceResolver($models);
$documentConverter = new \Stash\DocumentConverter($converter, $referencer, $models, $proxyAdapter);

$connection = new \Stash\Connection(new \MongoClient(), $models, $documentConverter);
$connection->selectDB('test');
```

Entity creation and storage:

```php
class Order
{
    private $id;
    private $customer;
    private $items;

    public function __construct($customer, $items)
    {
        $this->customer = $customer;
        $this->items = $items;
    }
}

class OrderItem
{
    private $name;
    private $amount;
    private $cost;

    public function __construct($name, $amount, $cost)
    {
        $this->name = $name;
        $this->amount = $amount;
        $this->cost = $cost;
    }
}

class Voucher
{
    private $name;
    private $discount;

    public function __construct($name, $discount)
    {
        $this->name = $name;
        $this->discount = $discount;
    }
}

class Customer
{
    private $name;
    private $address;

    public function __construct($name, CustomerAddress $address)
    {
        $this->$name = $name;
        $this->address = $address;
    }
}

class CustomerAddress
{
    private $address;
    private $city;
    private $zip;

    public function __construct($address, $city, $zip)
    {
        $this->address = $address;
        $this->city = $city;
        $this->zip = $zip;
    }
}

$order = new Order(
    new Customer('Joe Doe', new CustomerAddress('Mongo alley', 'Somewhere', '12345')),
    [
        new OrderItem('Foos', 10, 1000),
        new Voucher('Voucher', 250)
    ]
);

$connection->getCollection('order')->save($order);
```

And this is the stored MongoDB's semi-JSON representation.
When saving objects (entities), **Stash** adds the `_class` field, where it stores the class name

```
{
  "_id" : ObjectId("55746f4f87dee7bc0b000033"),
  "_class" : "Order",
  "customer" : {
    "_class" : "Customer",
    "address" : {
      "_class" : "CustomerAddress",
      "address" : "Mongo alley",
      "city" : "Somewhere",
      "zip" : "12345"
    }
  },
  "items" : [
    {
      "_class" : "OrderItem",
      "name" : "Foos",
      "amount" : 10,
      "cost" : 1000
    },
    {
      "_class" : "Voucher",
      "name" : "Voucher",
      "discount" : 250
    }
  ]
}                                                     
```

## Configuring proxy

By default, all required proxy classes are generated at runtime.
Generation uses a lot of reflection and it cause poor performance.
To prevent this, generated proxy classes can be reused:

```php
$config = new \ProxyManager\Configuration();
$config->setProxiesTargetDir(__DIR__ . '/generated/proxy/);
spl_autoload_register($config->getProxyAutoloader());

$proxyAdapter = new \Stash\ProxyAdapter($config);
```
