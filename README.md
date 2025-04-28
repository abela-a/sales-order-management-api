# 💵 Sales & Order API

Technical test for backend developer position at PT. Dibuiltadi Teknologi Kreatif. This repository contains a complete RESTful API for a Sales & Order System. The API manages customer, order, product, sales, and user, allowing operations such as creation, retrieval, update, and deletion of records. This project demonstrates clean code design, database schema implementation, unit testing, and performance tuning techniques.

---

## 📑 About this Project

This project showcases my backend development expertise, with a focus on designing and implementing robust, scalable, and maintainable APIs. Laravel is a highly popular framework widely adopted in production environments. It offers numerous built-in features that simplify handling a variety of use cases. Another reason for choosing Laravel is my extensive experience with it. I have been using the framework for over four years. This familiarity allows me to efficiently apply its best practices to deliver high-quality solutions.

### Tech Stack

-   Language: PHP `8.2`
-   Framework: Laravel `12.10`
-   Database: MySQL `8.0`
-   Documentation: Swagger
-   Testing: Pest

### Design Pattern

I use the Repository Design Pattern in this project. The Repository Design Pattern in Laravel is utilized to separate data access logic from the business logic of the application. This approach offers benefits such as more structured code, easier testing, and flexibility to accommodate changes. By using a repository, you can replace or modify the data source (e.g., switching from a database to an external API) without altering the business logic in controllers or services. Additionally, this pattern facilitates the implementation of SOLID principles, particularly the Dependency Inversion Principle, as controllers or services depend only on contracts (interfaces) rather than direct implementations. This makes the application easier to maintain and extend.

## ⚡ Getting Started

### Requirements

-   PHP >= 8.2
-   Composer
-   MySQL

### Installation

1. Clone the repository:

    ```sh
    git clone https://github.com/abela-a/sales-order-management-api.git
    ```

2. Navigate to the project directory:

    ```sh
    cd sales-order-management-api
    ```

3. Install dependencies:

    ```sh
    composer install
    ```

4. Set up the environment file:

    ```sh
    cp .env.example .env
    ```

    Configure the database and other environment variables as needed.

    ```env
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=technical_test_dibuiltadi
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. Generate app key for encryption:

    ```sh
    php artisan key:generate
    ```

6. Run migrations and seed the database:

    ```sh
    php artisan migrate
    ```

### Running the Project

To start the project locally, use the following command:

```sh
php artisan serve
```

---

## 🐳 Docker Setup

This project supports Docker deployment for easy setup and consistent environments across different machines.

### Prerequisites

-   Docker installed on your system
-   Docker Compose installed on your system

### Running with Docker

Build and start the containers:

```sh
docker-compose up -d
```

### Docker Services

The Docker setup includes the following services:

-   **app**: PHP application container
-   **db**: MySQL database container
-   **nginx**: Web server container

### Accessing the Application

After starting the Docker containers, you can access the application at:

```
http://localhost:8000
```

---

## 🔖 API Documentation

The API documentation is available via Swagger. Once the project is running, visit the following URL to explore the endpoints:

```
http://localhost:8000/api/documentation
```

---

## 📝 Testing

Unit and feature tests are implemented using Pest to ensure the reliability of the API. Run tests using:

### ⚠️ Testing Requirements

Before running tests, ensure you have a dedicated testing database configured. This prevents your development or production data from being affected during testing.

1. Add a testing database configuration to your `.env.testing` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=testing
DB_USERNAME=root
DB_PASSWORD=
```

2. Run the tests using one of the following commands:

```bash
php artisan test
# or
./vendor/bin/pest
# or HERD
herd coverage ./vendor/bin/pest
```

---

## 📧 Contact

I would greatly appreciate any feedback on my work, as it will help me improve and grow as a developer. If you have any comments, suggestions, or questions regarding this project, please feel free to reach out:

-   **Name**: Abel A Simanungkalit
-   **Email**: [work.abelardhana@gmail.com](mailto:work.abelardhana@gmail.com)
-   **GitHub Profile**: [https://github.com/abela-a](https://github.com/abela-a)

Thank you for taking the time to review this project!
