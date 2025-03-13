# YouQuote API Project - Step-by-Step Guide

## Phase 1: Setup and Planning (Day 1 - Morning)

1. **Set up development environment:**
    - Install PHP, Composer, and Laravel
    - Set up a local database (MySQL or PostgreSQL)
    - Configure your IDE (VS Code, PHPStorm, etc.)
    - Initialize Git repository
2. **Create a new Laravel project:**
    
    ```bash
    composer create-project laravel/laravel youquote-api
    cd youquote-api
    
    ```
    
3. **Plan the database schema:**
    - Design the Quote model (id, content, author, length, popularity_count, etc.)
    - If implementing authentication: Design User model and relationships
    - Create UML diagrams for your database schema

## Phase 2: Database Implementation (Day 1 - Afternoon)

1. **Create migrations:**
    
    ```bash
    php artisan make:migration create_quotes_table
    
    ```
    
    - Define the schema for quotes table in the migration file
2. **Create models:**
    
    ```bash
    php artisan make:model Quote
    
    ```
    
    - Define relationships and properties
3. **Create seeders and factories:**
    
    ```bash
    php artisan make:seeder QuoteSeeder
    php artisan make:factory QuoteFactory
    
    ```
    
    - Implement factory logic to generate sample quotes
    - Use seeders to populate the database with initial data
4. **Run migrations and seeders:**
    
    ```bash
    php artisan migrate
    php artisan db:seed
    
    ```
    

## Phase 3: Core API Implementation (Day 2)

1. **Create controllers:**
    
    ```bash
    php artisan make:controller QuoteController --api
    
    ```
    
2. **Implement CRUD operations:**
    - Create (POST /quotes)
    - Read (GET /quotes, GET /quotes/{id})
    - Update (PUT /quotes/{id})
    - Delete (DELETE /quotes/{id})
3. **Implement request validation:**
    
    ```bash
    php artisan make:request StoreQuoteRequest
    php artisan make:request UpdateQuoteRequest
    
    ```
    
    - Define validation rules for input data
4. **Implement random quotes functionality:**
    - Add method to controller for getting random quotes
    - Create route (GET /quotes/random?count=1)
5. **Implement quote filtering by length:**
    - Add filtering functionality to the controller
    - Create route (GET /quotes?min_length=10&max_length=50)
6. **Implement quote popularity tracking:**
    - Add middleware or controller logic to track quote requests
    - Create route for getting popular quotes (GET /quotes/popular)

## Phase 4: Testing (Day 3)

1. **Create API tests:**
    
    ```bash
    php artisan make:test QuoteApiTest
    
    ```
    
    - Test CRUD operations
    - Test random quotes functionality
    - Test filtering functionality
    - Test popularity tracking
2. **Run tests and fix issues:**
    
    ```bash
    php artisan test
    
    ```
    

## Phase 5: Bonus Features (Day 4)

1. **Implement authentication (optional):**
    
    ```bash
    php artisan make:controller AuthController
    
    ```
    
    - Set up JWT for authentication
    - Create routes for registration, login, and logout
    - Secure routes with auth middleware
2. **Implement quote image generation:**
    - Install Intervention Image library:
    
    ```bash
    composer require intervention/image
    
    ```
    
    - Create controller/service for image generation
    - Create route for generating images (GET /quotes/{id}/image)

## Phase 6: Documentation and Finalization (Day 5)

1. **Create API documentation:**
    - Document all endpoints, parameters, and responses
    - Use tools like Swagger/OpenAPI or create a Markdown file
2. **Finalize GitHub repository:**
    - Ensure code follows best practices (PSR standards)
    - Add README.md with project overview and setup instructions
    - Review and refactor code as needed
3. **Prepare for deployment:**
    - Configure environment variables
    - Set up production database
    - Configure web server (Nginx/Apache)
4. **Deploy to cloud service:**
    - Set up server on AWS EC2, Azure VM, or DigitalOcean
    - Deploy Laravel application
    - Configure HTTPS
5. **Prepare for presentation:**
    - Test all functionality in production environment
    - Prepare demonstration scenarios
    - Practice code review explanation
    - Practice role-playing presentation (in French)

## Detailed API Endpoints

1. **Quotes Management:**
    - `GET /api/quotes`: Get all quotes
    - `GET /api/quotes/{id}`: Get a specific quote
    - `POST /api/quotes`: Create a new quote
    - `PUT /api/quotes/{id}`: Update a quote
    - `DELETE /api/quotes/{id}`: Delete a quote
2. **Random Quotes:**
    - `GET /api/quotes/random?count=5`: Get 5 random quotes
3. **Quote Filtering:**
    - `GET /api/quotes?min_length=10&max_length=50`: Get quotes with 10-50 words
4. **Popular Quotes:**
    - `GET /api/quotes/popular`: Get the most requested quotes
5. **Authentication (Bonus):**
    - `POST /api/register`: Register a new user
    - `POST /api/login`: Log in a user
    - `POST /api/logout`: Log out a user
6. **Image Generation (Bonus):**
    - `GET /api/quotes/{id}/image`: Generate image for a quote