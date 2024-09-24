# Database Playground

This platform allows you to exercise your SQL skills through a simple gamification system.

## Development

### Preparation

- Use PhpStorm for PHP and Twig development, and VS Code for TypeScript development.
- [Devenv](https://devenv.sh) provides the development environment, including pre-commit hooks, PHP, and Node.js runtimes.
- [Docker Compose](https://docs.docker.com/compose/) sets up the development runtime environment.

### Setup

1. Clone the repository.
2. Run `devenv up` to start the development environment.
3. Execute `composer install` to install the PHP dependencies.
4. Run `pnpm install` to install the Node.js dependencies. This step is optional but helpful if you prefer type declarations when developing TypeScript.
5. Start the database, Redis, and PHP runtime by running `docker compose up -d`.
6. Create an administrator user with the command: `php ./bin/console app:create-user -p "yourpassword" -r "ROLE_ADMIN" "admin" "admin@youremail.tld"`.
7. (Optional) Import the schema and questions by running `php ./bin/console app:import schema.json`.
8. Navigate to `https://localhost` to access the application. Use `https://localhost/admin` to access the admin panel.

## Deployment

1. Deploy Redis, PostgreSQL, Meilisearch, and Umami (for statistics) on Zeabur.
2. Deploy the application in Git mode on Zeabur.
3. Add the following environment variables to the application:
   ```env
   DATABASE_URL=postgresql://${POSTGRES_USERNAME}:${POSTGRES_PASSWORD}@postgresql.zeabur.internal:5432/${POSTGRES_DATABASE}?serverVersion=16&charset=utf8
   SERVER_NAME=:${PORT}
   APP_SECRET=${PASSWORD}
   MEILISEARCH_URL=http://meilisearch.zeabur.internal:7700
   MEILISEARCH_API_KEY=${MEILI_MASTER_KEY}
   ```
4. Create an index in Meilisearch by running:
   ```
   php bin/console meili:create --update-settings
   ```
5. Bind your domain, and it will be ready for use.
