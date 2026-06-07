# Ecommerce Store API - Laravel 12 Backend

A robust RESTful API for an eCommerce platform built with Laravel 12, featuring authentication, product management, order processing, and admin dashboard capabilities.

## 🚀 Features

### Authentication & Authorization
- User registration and login with JWT-like Sanctum tokens
- Role-based access control (Admin/Customer)
- Profile management and password change
- Email unique validation

### Customer Features
- Browse products with filtering and search
- View product details
- Place orders with multiple items
- View order history and order details
- Update profile information

### Admin Features
- Complete CRUD operations for categories
- Complete CRUD operations for products
- Stock management
- Order status management (pending, processing, shipped, delivered, cancelled)
- Customer management (view, update, delete, status toggle)
- Dashboard statistics and analytics
- Low stock product alerts

### Additional Features
- Image upload for categories and products
- Soft deletes for safe data removal
- Pagination, sorting, and filtering on all listing endpoints
- Order number generation
- Stock tracking with automatic updates on orders
- Tax calculation (10%)
- Shipping fee management

## 📋 Prerequisites

- PHP >= 8.2
- Composer
- MySQL >= 5.7 or MariaDB >= 10.2
- Node.js & NPM (for frontend integration)

## 🛠 Installation

### Step 1: Clone and Setup

```bash
# Create new Laravel project
composer create-project laravel/laravel:^12 ecommerceStore
cd ecommerceStore

# Install required packages
composer require laravel/sanctum
composer require spatie/laravel-permission
composer require intervention/image
```

### Step 2: Environment Configuration

Create your `.env` file and configure:

```env
APP_NAME="Ecommerce Store"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_store_db
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:5174
SESSION_DOMAIN=localhost
```

### Step 3: Run Migrations and Seeders

```bash
# Run migrations
php artisan migrate

# Run seeders (creates admin, categories, products)
php artisan migrate:fresh --seed

# Create storage link for images
php artisan storage:link

# Start the development server
php artisan serve
```

## 🔑 Default Admin Credentials

After running seeders, you can login with:

- **Email:** admin@example.com
- **Password:** password

## 📁 Database Structure

### Tables
- **users** - User accounts (admin/customer)
- **categories** - Product categories
- **products** - Product listings with stock tracking
- **orders** - Order information
- **order_items** - Individual items within orders
- **personal_access_tokens** - Sanctum token management

### Key Relationships
- Categories have many Products
- Products belong to Categories
- Orders belong to Customers (Users)
- Order Items belong to Orders and Products

## 🔗 API Endpoints

### Public Routes (No Authentication)

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | Register new customer |
| POST | `/api/login` | Login user |
| GET | `/api/categories` | List all categories |
| GET | `/api/categories/{id}` | Get category details |
| GET | `/api/products` | List all products |
| GET | `/api/products/{id}` | Get product details |

### Protected Routes (Authentication Required)

#### User Profile
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/logout` | Logout user |
| GET | `/api/profile` | Get user profile |
| PUT | `/api/profile` | Update profile |
| PUT | `/api/change-password` | Change password |

#### Orders
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/orders` | List orders (admin: all, customer: own) |
| POST | `/api/orders` | Create new order |
| GET | `/api/orders/{id}` | Get order details |
| GET | `/api/my-orders` | Get customer's orders |

### Admin Only Routes

#### Dashboard
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/dashboard/stats` | Get dashboard statistics |

#### Category Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/categories` | Create category |
| PUT | `/api/categories/{id}` | Update category |
| DELETE | `/api/categories/{id}` | Delete category |

#### Product Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/products` | Create product |
| PUT | `/api/products/{id}` | Update product |
| DELETE | `/api/products/{id}` | Delete product |
| PUT | `/api/products/{product}/stock` | Update stock quantity |

#### Order Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| PUT | `/api/orders/{order}/status` | Update order status |

#### Customer Management
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/customers` | List all customers |
| GET | `/api/customers/{id}` | Get customer details |
| PUT | `/api/customers/{id}` | Update customer |
| PUT | `/api/customers/{user}/status` | Toggle customer status |
| DELETE | `/api/customers/{user}` | Delete customer |

## 📝 API Usage Examples

### Register a Customer

```bash
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "1234567890",
    "address": "123 Main St"
}
```

### Login

```bash
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

### Create an Order

```bash
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json

{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        }
    ],
    "shipping_fee": 10.00,
    "payment_method": "cash_on_delivery",
    "notes": "Please call before delivery"
}
```

### Update Order Status (Admin)

```bash
PUT /api/orders/1/status
Authorization: Bearer {admin_token}
Content-Type: application/json

{
    "order_status": "processing",
    "payment_status": "paid"
}
```

### Create Product (Admin)

```bash
POST /api/products
Authorization: Bearer {admin_token}
Content-Type: multipart/form-data

{
    "category_id": 1,
    "sku": "PROD-001",
    "name": "Smartphone X",
    "description": "Latest smartphone with amazing features",
    "price": 599.99,
    "cost_price": 450.00,
    "stock_qty": 50,
    "active": true,
    "image": (file)
}
```

## 🎯 Query Parameters for Listings

All listing endpoints support pagination, sorting, and filtering:

### Products
- `search` - Search by name or SKU
- `category_id` - Filter by category
- `min_price` - Minimum price filter
- `max_price` - Maximum price filter
- `in_stock` - Filter by stock status (true/false)
- `sort_by` - Sort field (name, price, created_at, etc.)
- `sort_order` - Sort direction (asc/desc)
- `per_page` - Items per page (default: 15)

### Orders
- `order_status` - Filter by status
- `payment_status` - Filter by payment status
- `customer_id` - Filter by customer (admin only)
- `from_date` - Filter orders from date
- `to_date` - Filter orders until date

### Customers (Admin only)
- `search` - Search by name, email, or phone
- `sort_by` - Sort field
- `sort_order` - Sort direction

## 📊 Dashboard Statistics Response

```json
{
    "total_orders": 150,
    "total_revenue": 45750.00,
    "total_products": 45,
    "total_customers": 120,
    "recent_orders": [...],
    "low_stock_products": [...],
    "monthly_revenue": [...],
    "top_products": [...]
}
```

## 🔒 Security Features

- Sanctum token-based authentication
- Role-based middleware for admin routes
- Password hashing with bcrypt
- Input validation using Form Requests
- Protection against mass assignment
- SQL injection prevention via Eloquent
- XSS protection

## 🧪 Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=OrderTest
```

## 📦 Deployment

### Production Checklist

```bash
# Optimize configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set production environment variables
APP_ENV=production
APP_DEBUG=false

# Run migrations
php artisan migrate --force
```

## 🤝 Support

For issues or questions:
1. Check the Laravel 12 documentation
2. Review the API response messages
3. Ensure all prerequisites are met
4. Verify database connections

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 👨‍💻 Author

Built with Laravel 12 - A powerful PHP framework for web artisans.

---

**Note:** This is the backend API only. For the frontend implementation, you'll need to connect this API to a frontend application (React, Vue.js, or mobile app).
