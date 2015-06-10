# Stash - MongoDB ODM

**Stash** is an object-document mapper for MongoDB written in PHP.
It adds fully transparent persistence layer while still preserving easy way how MongoDB is handled.   

This means that MongoDB can be used in almost exact same way as it would be used with arrays.
But with small but important differences - instead of returning plain arrays, **Stash** will return objects - entites.
Since **Stash** returns entities it also can store them.

## Next steps

 - event dispatcher
 - references
 
## Example

Model definitions

```php
$models = new ModelCollection();
$models->register(
    new Model(
        '\Order',
        [
            new Id(),
            new Document('customer'),
            new ArrayOf('items', Fields::TYPE_DOCUMENT)
        ]
    ),
    'order'
);

$models->register(
    new Model(
        '\OrderItem',
        [
            new Scalar('name', Fields::TYPE_STRING),
            new Scalar('amount', Fields::TYPE_INTEGER),
            new Scalar('cost', Fields::TYPE_INTEGER)
        ]
    )
);

$models->register(
    new Model(
        '\Voucher',
        [
            new Scalar('name', Fields::TYPE_STRING),
            new Scalar('cost', Fields::TYPE_INTEGER)
        ]
    )
);

$models->register(
    new Model(
        '\Customer',
        [
            new Scalar('name', Fields::TYPE_STRING),
            new Document('address')
        ]
    )
);

$models->register(
    new Model(
        '\CustomerAddress',
        [
            new Scalar('address', Fields::TYPE_STRING),
            new Scalar('city', Fields::TYPE_STRING),
            new Scalar('zip', Fields::TYPE_STRING)
        ]
    )
);
```

Connection to database:

```php
$connection = new Connection(
    new MongoClient(),
    'test',
    new DocumentConverter(new Converter(), $models),
    $models
);
```

Creating entity and how to store it:

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

And this is stored BSON representation.
When saving objects - entities **Stash** adds `_class` field, where it stores class name

```json
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
