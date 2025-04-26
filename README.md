# Backend Dictionary API

This is a Laravel API project that acts as a proxy to the Words API.

## Setup Instructions
 
### 1. Install dependencies (Optional)

```bash
composer install
```

### 2. Configure Environment Variables

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Then edit the `.env` file and set your Words API Key:

```env
WORDS_API_KEY=your-words-api-key-here
```

### 3. Build and run the project using Docker

```bash
docker-compose up --build
```

The application will be available at [http://localhost:8000](http://localhost:8000).

### 4. Access the container bash

Open a bash session inside the app container:

```bash
docker-compose exec app bash
```

### 5. Run the Import Words command

Inside the container, execute:

```bash
php artisan importWords
```

---
