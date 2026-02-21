# Netflix Clone

A full-stack Netflix clone application built with Laravel and Vue.js.

## Features

- **Movie/Series Browsing**: Browse content in a Netflix-style interface.
- **Carousels**: Smooth sliders for content categories using Swiper.js.
- **Responsive Design**: Fully responsive UI built with Tailwind CSS.
- **Modern Stack**: Powered by Laravel 9 (API) and Vue.js 3 (Frontend).
- **Dockerized**: easy setup with Laravel Sail.

## Tech Stack

- **Backend**: Laravel 9, PHP 8
- **Frontend**: Vue.js 3, Vue Router, Vuex
- **Styling**: Tailwind CSS, Bootstrap 5
- **Database**: MySQL
- **DevOps**: Docker, Laravel Sail

## Getting Started

### Prerequisites

- Docker Desktop
- WSL 2 (if on Windows)

### Installation & Run

1.  **Clone the repository**:
    ```bash
    git clone <repository-url>
    cd netflix-main
    ```

2.  **Install Dependencies** (using Sail):
    ```bash
    # Install Composer dependencies
    docker run --rm \
        -u "$(id -u):$(id -g)" \
        -v ./:/var/www/html \
        -w /var/www/html \
        laravelsail/php81-composer:latest \
        composer install --ignore-platform-reqs
    ```

3.  **Start the Application**:
    ```bash
    ./vendor/bin/sail up -d
    ```

4.  **Setup Frontend**:
    ```bash
    ./vendor/bin/sail npm install
    ./vendor/bin/sail npm run dev
    ```

5.  **Database Migration**:
    ```bash
    ./vendor/bin/sail artisan migrate:fresh --seed
    ```

6.  **Access**:
    Open [http://localhost](http://localhost) in your browser.

## Project Structure

- `app/`: Laravel backend logic (Models, Controllers).
- `resources/js/`: Vue.js frontend application.
- `resources/views/`: Blade templates (entry point).
- `routes/`: API and Web routes.
- `docker-compose.yml`: Docker configuration.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
