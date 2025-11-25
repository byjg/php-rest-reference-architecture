---
sidebar_position: 110
---

# Service Layer Patterns

The Service layer acts as an orchestration layer between REST controllers and repositories, providing business logic, validation, and transaction management.

## Table of Contents

- [Overview](#overview)
- [Architectural Pattern](#architectural-pattern)
- [BaseService Features](#baseservice-features)
- [Creating Services](#creating-services)
- [Business Logic Patterns](#business-logic-patterns)
- [Validation](#validation)
- [Transaction Management](#transaction-management)
- [Service Composition](#service-composition)
- [Error Handling](#error-handling)
- [Testing Services](#testing-services)

## Overview

The Service layer provides:

- **Business Logic**: Domain-specific operations beyond basic CRUD
- **Validation**: Data validation before persistence
- **Orchestration**: Coordinating multiple repositories
- **Transaction Boundaries**: Managing database transactions
- **Reusability**: Sharing logic across multiple controllers

**Location**: `src/Service/BaseService.php`

## Architectural Pattern

### Always Call Service from Controllers

**Rule**: REST controllers should ALWAYS call the Service layer, never the Repository directly.

```php
// ✓ CORRECT - Controller calls Service
#[ValidateRequest]
public function putDummyHex(HttpResponse $response, HttpRequest $request): void
{
    $dummyHexService = Config::get(DummyHexService::class);
    $model = $dummyHexService->update(ValidateRequest::getPayload());
    $response->write($model);
}

// ✗ WRONG - Controller calls Repository directly
public function putDummyHex(HttpResponse $response, HttpRequest $request): void
{
    $repository = Config::get(DummyHexRepository::class);
    $model = $repository->get($id);  // Don't do this!
    // ...
}
```

### Why This Pattern?

**1. Consistency**
- Controllers don't need to decide "Service or Repository?"
- Single entry point for all business operations
- Predictable code structure across the application

**2. Single Responsibility**
- Controllers focus on HTTP concerns (request/response handling)
- Services handle business logic
- Repositories handle data access

**3. Extensibility**
- Add business logic later without changing controllers
- Example: `get()` is simple now, but you might add caching, logging, or access control later

**4. Testability**
- Mock one Service instead of deciding between Service and Repository
- Consistent test structure across all endpoints

### BaseService as a Wrapper

Many `BaseService` methods are simple wrappers around Repository methods:

```php
// Simple wrapper - no additional logic
public function get(array|string|int|LiteralInterface $id): mixed
{
    return $this->repository->get($id);
}

// Adds business logic - validation
public function create(array $payload): mixed
{
    $primaryKey = $this->repository->getMapper()->getPrimaryKey();

    // Business rule: create should not include PK
    foreach ($primaryKey as $pkField) {
        if (!empty($payload[$pkField])) {
            throw new Error422Exception(
                "Create should not include primary key field: {$pkField}"
            );
        }
    }

    $model = $this->repository->getMapper()->getEntity($payload);
    $this->repository->save($model);
    return $model;
}
```

**This is intentional and good:**
- Provides a consistent interface
- Allows adding logic later without breaking controllers
- Makes the codebase easier to understand and maintain

### When You Need Direct Repository Access

For complex queries or operations not covered by BaseService:

```php
class DummyService extends BaseService
{
    public function findActiveByCategory(string $category): array
    {
        // Access repository for custom query
        $query = Query::getInstance()
            ->table($this->repository->getMapper()->getTable())
            ->where('category = :category', ['category' => $category])
            ->where('status = :status', ['status' => 'active']);

        return $this->repository->getByQuery($query);
    }
}
```

Or use the repository property directly in your extended service:

```php
class DummyService extends BaseService
{
    public function getStatistics(): array
    {
        $executor = $this->repository->getExecutor();
        $result = $executor->getScalar('SELECT COUNT(*) FROM dummy');
        return ['count' => $result];
    }
}
```

### Summary

- **Controllers → Service** (always)
- **Service → Repository** (simple wrappers + business logic)
- **Repository → Database** (data access)

This three-tier architecture keeps code organized, testable, and maintainable.

## BaseService Features

All services extending `BaseService` have these standard methods:

```php
abstract class BaseService
{
    protected BaseRepository $repository;

    public function __construct(BaseRepository $repository)
    {
        $this->repository = $repository;
    }

    // Retrieve methods
    public function get($id);                                    // Returns model or null
    public function getOrFail($id);                              // Returns model or throws 404
    public function list(?int $page, ?int $size): array;         // List with pagination

    // Mutation methods
    public function create(array $payload);                      // Create from array
    public function update(array $payload);                      // Update from array
    public function save($model): void;                          // Save model object
    public function delete($id): void;                           // Delete by ID
}
```

### Example Service

```php
<?php

namespace RestReferenceArchitecture\Service;

use RestReferenceArchitecture\Repository\DummyRepository;

class DummyService extends BaseService
{
    public function __construct(DummyRepository $repository)
    {
        parent::__construct($repository);
    }

    // Add custom business logic here
}
```

## Creating Services

### Basic Service Structure

```php
<?php

namespace RestReferenceArchitecture\Service;

use RestReferenceArchitecture\Repository\ProductRepository;
use RestReferenceArchitecture\Model\Product;

class ProductService extends BaseService
{
    public function __construct(ProductRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Apply discount to product
     */
    public function applyDiscount(int $productId, float $percentage): Product
    {
        $product = $this->getOrFail($productId);

        $originalPrice = $product->getPrice();
        $discountedPrice = $originalPrice * (1 - $percentage / 100);

        $product->setPrice($discountedPrice);
        $product->setOriginalPrice($originalPrice);

        $this->baseRepository->save($product);

        return $product;
    }

    /**
     * Check if product is in stock
     */
    public function isInStock(int $productId): bool
    {
        $product = $this->getOrFail($productId);
        return $product->getStock() > 0;
    }
}
```

### Register in Dependency Injection

Add to `config/05-services/01-services.php`:

```php
use RestReferenceArchitecture\Service\ProductService;

return [
    ProductService::class => fn() => new ProductService(
        Config::get(ProductRepository::class)
    )
];
```

## BaseService Methods

### get() vs getOrFail()

**Location**: `src/Service/BaseService.php:18,23`

```php
// get() - Returns null if not found
$product = $service->get($id);
if ($product === null) {
    // Handle not found
}

// getOrFail() - Throws Error404Exception if not found
try {
    $product = $service->getOrFail($id);
    // Guaranteed to have a product here
} catch (Error404Exception $e) {
    // Handle not found
}
```

**Best Practice**: Use `getOrFail()` in REST endpoints to automatically return 404 responses.

### create() Method

Creates a model from an associative array:

**Location**: `src/Service/BaseService.php:37`

```php
public function create(array $payload)
{
    // Converts array to model using mapper
    $model = $this->repository->getMapper()->getEntity($payload);

    // Saves to database
    $this->repository->save($model);

    return $model;
}
```

#### Usage

```php
$payload = [
    'name' => 'Product Name',
    'price' => 99.99,
    'stock' => 100
];

$product = $service->create($payload);
echo $product->getId(); // Auto-generated ID
```

### update() Method

Updates an existing model from an array:

**Location**: `src/Service/BaseService.php:44`

```php
public function update(array $payload)
{
    // Get existing model (throws 404 if not found)
    $model = $this->getOrFail($payload['id'] ?? null);

    // Copy payload properties to model
    ObjectCopy::copy($payload, $model);

    // Save changes
    $this->repository->save($model);

    return $model;
}
```

#### Usage

```php
$payload = [
    'id' => 5,
    'name' => 'Updated Name',
    'price' => 89.99
];

$product = $service->update($payload);
```

**Note**: Only properties present in the payload are updated. Missing properties retain their current values.

### save() Method

Saves a model object directly:

**Location**: `src/Service/BaseService.php:52`

```php
$product = $service->get($id);
$product->setPrice(79.99);
$product->setStock(50);

$service->save($product);
```

### list() Method

Returns paginated list:

**Location**: `src/Service/BaseService.php:32`

```php
// Default pagination (page 0, size 20)
$products = $service->list();

// Custom pagination
$products = $service->list(page: 2, size: 50);
```

## Business Logic Patterns

### Domain Logic in Services

Keep business rules in the service layer:

```php
class OrderService extends BaseService
{
    /**
     * Place an order with business rules
     */
    public function placeOrder(array $orderData): Order
    {
        // Business validation
        if ($orderData['amount'] < 0) {
            throw new Error400Exception('Order amount must be positive');
        }

        // Create order
        $order = $this->create($orderData);

        // Business logic: Update inventory
        $this->updateInventory($order);

        // Business logic: Send notification
        $this->sendOrderConfirmation($order);

        return $order;
    }

    protected function updateInventory(Order $order): void
    {
        $productService = Config::get(ProductService::class);

        foreach ($order->getItems() as $item) {
            $product = $productService->getOrFail($item->getProductId());
            $newStock = $product->getStock() - $item->getQuantity();

            if ($newStock < 0) {
                throw new Error400Exception(
                    "Insufficient stock for product: {$product->getName()}"
                );
            }

            $product->setStock($newStock);
            $productService->save($product);
        }
    }

    protected function sendOrderConfirmation(Order $order): void
    {
        $emailService = Config::get(EmailService::class);
        $emailService->sendOrderConfirmation($order);
    }
}
```

### Calculated Properties

```php
class InvoiceService extends BaseService
{
    /**
     * Calculate invoice totals
     */
    public function calculateTotals(int $invoiceId): array
    {
        $invoice = $this->getOrFail($invoiceId);

        $subtotal = 0;
        foreach ($invoice->getItems() as $item) {
            $subtotal += $item->getPrice() * $item->getQuantity();
        }

        $taxRate = $this->getTaxRate($invoice->getRegion());
        $tax = $subtotal * $taxRate;
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total
        ];
    }

    protected function getTaxRate(string $region): float
    {
        return match($region) {
            'US-CA' => 0.0725,
            'US-NY' => 0.08875,
            'US-TX' => 0.0625,
            default => 0.05
        };
    }
}
```

### State Transitions

```php
class OrderService extends BaseService
{
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Confirm order with business rules
     */
    public function confirmOrder(int $orderId): Order
    {
        $order = $this->getOrFail($orderId);

        // Validate state transition
        if ($order->getStatus() !== self::STATUS_PENDING) {
            throw new Error400Exception(
                'Only pending orders can be confirmed'
            );
        }

        // Check payment
        $paymentService = Config::get(PaymentService::class);
        if (!$paymentService->isPaymentReceived($orderId)) {
            throw new Error400Exception('Payment not received');
        }

        // Transition state
        $order->setStatus(self::STATUS_CONFIRMED);
        $order->setConfirmedAt(new \DateTime());

        $this->save($order);

        // Trigger follow-up actions
        $this->notifyWarehouse($order);

        return $order;
    }

    /**
     * Cancel order with validation
     */
    public function cancelOrder(int $orderId, string $reason): Order
    {
        $order = $this->getOrFail($orderId);

        // Business rule: Can't cancel shipped orders
        if (in_array($order->getStatus(), [self::STATUS_SHIPPED, self::STATUS_DELIVERED])) {
            throw new Error400Exception('Cannot cancel shipped orders');
        }

        $order->setStatus(self::STATUS_CANCELLED);
        $order->setCancellationReason($reason);
        $order->setCancelledAt(new \DateTime());

        $this->save($order);

        // Refund if paid
        $this->processRefund($order);

        return $order;
    }
}
```

## Validation

### Pre-Save Validation

```php
class UserService extends BaseService
{
    public function create(array $payload)
    {
        // Validate business rules
        $this->validateEmail($payload['email'] ?? '');
        $this->validateUniqueEmail($payload['email']);
        $this->validatePasswordStrength($payload['password'] ?? '');

        // Create user
        $user = parent::create($payload);

        // Hash password
        $user->setPassword(password_hash($payload['password'], PASSWORD_DEFAULT));

        $this->save($user);

        return $user;
    }

    protected function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Error400Exception('Invalid email address');
        }
    }

    protected function validateUniqueEmail(string $email): void
    {
        $existing = $this->repository->findByEmail($email);
        if ($existing) {
            throw new Error400Exception('Email already exists');
        }
    }

    protected function validatePasswordStrength(string $password): void
    {
        if (strlen($password) < 8) {
            throw new Error400Exception('Password must be at least 8 characters');
        }

        if (!preg_match('/[A-Z]/', $password)) {
            throw new Error400Exception('Password must contain uppercase letter');
        }

        if (!preg_match('/[0-9]/', $password)) {
            throw new Error400Exception('Password must contain a number');
        }
    }
}
```

### Custom Validation Methods

```php
class ProductService extends BaseService
{
    public function update(array $payload)
    {
        // Validate before updating
        $this->validatePrice($payload['price'] ?? null);
        $this->validateStock($payload['stock'] ?? null);

        return parent::update($payload);
    }

    protected function validatePrice(?float $price): void
    {
        if ($price !== null && $price < 0) {
            throw new Error400Exception('Price cannot be negative');
        }
    }

    protected function validateStock(?int $stock): void
    {
        if ($stock !== null && $stock < 0) {
            throw new Error400Exception('Stock cannot be negative');
        }
    }
}
```

## Transaction Management

### Single Service Transactions

```php
class OrderService extends BaseService
{
    /**
     * Create order with items in a transaction
     */
    public function createOrderWithItems(array $orderData, array $items): Order
    {
        $executor = $this->repository->getExecutorWrite();

        try {
            $executor->beginTransaction();

            // Create order
            $order = $this->create($orderData);

            // Create order items
            $orderItemService = Config::get(OrderItemService::class);
            foreach ($items as $itemData) {
                $itemData['order_id'] = $order->getId();
                $orderItemService->create($itemData);
            }

            $executor->commitTransaction();

            return $order;

        } catch (\Exception $e) {
            $executor->rollbackTransaction();
            throw $e;
        }
    }
}
```

### Multi-Service Transactions

```php
class OrderService extends BaseService
{
    /**
     * Process order affecting multiple entities
     */
    public function processOrder(array $orderData): Order
    {
        $executor = $this->repository->getExecutorWrite();

        try {
            $executor->beginTransaction();

            // 1. Create order
            $order = $this->create($orderData);

            // 2. Reduce product inventory
            $productService = Config::get(ProductService::class);
            foreach ($orderData['items'] as $item) {
                $productService->reduceStock($item['product_id'], $item['quantity']);
            }

            // 3. Create invoice
            $invoiceService = Config::get(InvoiceService::class);
            $invoice = $invoiceService->createFromOrder($order);

            // 4. Record payment
            $paymentService = Config::get(PaymentService::class);
            $paymentService->recordPayment([
                'order_id' => $order->getId(),
                'amount' => $invoice->getTotal()
            ]);

            $executor->commitTransaction();

            return $order;

        } catch (\Exception $e) {
            $executor->rollbackTransaction();
            throw $e;
        }
    }
}
```

### Transaction Helper Trait

```php
trait TransactionAware
{
    protected function inTransaction(callable $callback)
    {
        $executor = $this->repository->getExecutorWrite();

        try {
            $executor->beginTransaction();
            $result = $callback();
            $executor->commitTransaction();
            return $result;
        } catch (\Exception $e) {
            $executor->rollbackTransaction();
            throw $e;
        }
    }
}

// Usage
class OrderService extends BaseService
{
    use TransactionAware;

    public function processOrder(array $data): Order
    {
        return $this->inTransaction(function() use ($data) {
            $order = $this->create($data);
            // More operations...
            return $order;
        });
    }
}
```

## Service Composition

### Calling Other Services

```php
class OrderService extends BaseService
{
    protected ProductService $productService;
    protected CustomerService $customerService;
    protected PaymentService $paymentService;

    public function __construct(
        OrderRepository $repository,
        ProductService $productService,
        CustomerService $customerService,
        PaymentService $paymentService
    ) {
        parent::__construct($repository);
        $this->productService = $productService;
        $this->customerService = $customerService;
        $this->paymentService = $paymentService;
    }

    public function createOrder(array $orderData): Order
    {
        // Validate customer exists
        $customer = $this->customerService->getOrFail($orderData['customer_id']);

        // Validate products
        foreach ($orderData['items'] as $item) {
            $product = $this->productService->getOrFail($item['product_id']);

            if (!$this->productService->isInStock($product->getId())) {
                throw new Error400Exception(
                    "Product out of stock: {$product->getName()}"
                );
            }
        }

        // Create order
        $order = $this->create($orderData);

        // Process payment
        $this->paymentService->charge([
            'customer_id' => $customer->getId(),
            'amount' => $order->getTotal()
        ]);

        return $order;
    }
}
```

### Dependency Injection Setup

Register in `config/05-services/01-services.php`:

```php
use RestReferenceArchitecture\Service\OrderService;

return [
    OrderService::class => fn() => new OrderService(
        Config::get(OrderRepository::class),
        Config::get(ProductService::class),
        Config::get(CustomerService::class),
        Config::get(PaymentService::class)
    )
];
```

### Lazy Loading Services

```php
class OrderService extends BaseService
{
    protected function getProductService(): ProductService
    {
        return Config::get(ProductService::class);
    }

    protected function getCustomerService(): CustomerService
    {
        return Config::get(CustomerService::class);
    }

    public function createOrder(array $orderData): Order
    {
        // Services loaded only when needed
        $customer = $this->getCustomerService()->getOrFail($orderData['customer_id']);
        $product = $this->getProductService()->getOrFail($orderData['product_id']);

        // ...
    }
}
```

## Error Handling

### Throwing Appropriate Exceptions

```php
use ByJG\RestServer\Exception\Error400Exception;
use ByJG\RestServer\Exception\Error404Exception;
use ByJG\RestServer\Exception\Error409Exception;

class ProductService extends BaseService
{
    public function create(array $payload)
    {
        // 400 - Bad Request
        if (empty($payload['name'])) {
            throw new Error400Exception('Product name is required');
        }

        // 409 - Conflict
        if ($this->repository->existsBySku($payload['sku'])) {
            throw new Error409Exception('Product SKU already exists');
        }

        return parent::create($payload);
    }

    public function delete($id): void
    {
        $product = $this->getOrFail($id); // 404 if not found

        // 400 - Business rule violation
        if ($this->hasActiveOrders($product->getId())) {
            throw new Error400Exception(
                'Cannot delete product with active orders'
            );
        }

        parent::delete($id);
    }
}
```

### Custom Exceptions

```php
namespace RestReferenceArchitecture\Exception;

class InsufficientStockException extends \ByJG\RestServer\Exception\Error400Exception
{
    public function __construct(string $productName, int $available, int $requested)
    {
        parent::__construct(
            "Insufficient stock for {$productName}. Available: {$available}, Requested: {$requested}"
        );
    }
}

// Usage
if ($product->getStock() < $quantity) {
    throw new InsufficientStockException(
        $product->getName(),
        $product->getStock(),
        $quantity
    );
}
```

## Testing Services

### Unit Testing

```php
use PHPUnit\Framework\TestCase;

class ProductServiceTest extends TestCase
{
    protected ProductService $service;
    protected ProductRepository $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ProductRepository::class);
        $this->service = new ProductService($this->repository);
    }

    public function testApplyDiscount()
    {
        $product = new Product();
        $product->setId(1);
        $product->setPrice(100.00);

        $this->repository
            ->expects($this->once())
            ->method('get')
            ->with(1)
            ->willReturn($product);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function($p) {
                return $p->getPrice() === 90.00;
            }));

        $result = $this->service->applyDiscount(1, 10);

        $this->assertEquals(90.00, $result->getPrice());
        $this->assertEquals(100.00, $result->getOriginalPrice());
    }
}
```

### Integration Testing

See [Testing Guide](testing-guide.md) for complete testing documentation.

## Best Practices

1. **Keep Controllers Thin**: Move logic to services
2. **Single Responsibility**: One service per entity or domain concept
3. **Use getOrFail()**: In REST endpoints for automatic 404 responses
4. **Transaction Boundaries**: Use transactions for multi-step operations
5. **Validate Early**: Check business rules before database operations
6. **Type Hints**: Always use type hints for better IDE support
7. **Document Complex Logic**: Add PHPDoc for business rules
8. **Avoid Repository Leakage**: Don't return repository objects from services
9. **Service Composition**: Inject related services via constructor
10. **Test Business Logic**: Write unit tests for service methods

## Related Documentation

- [Advanced Repository Patterns](repository-advanced.md)
- [REST API Development](rest.md)
- [Error Handling](error-handling.md)
- [Testing Guide](testing-guide.md)
