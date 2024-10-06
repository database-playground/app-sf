# Database Playground

The Database Playground is a platform designed to enhance your SQL skills through an engaging gamification system.

## Development

### Preparation

- Use **PhpStorm** for PHP and Twig development, and **VS Code** for TypeScript development.
- **Devenv** provides a comprehensive development environment, including pre-commit hooks and runtimes for PHP and
  Node.js. Learn more at [Devenv](https://devenv.sh).
- **Docker Compose** is utilized to set up the development runtime environment. For more details, visit
  the [Docker Compose documentation](https://docs.docker.com/compose/).

### Setup

1. Clone the repository.
2. Run `devenv up` to start the development environment.
3. Execute `composer install` to install PHP dependencies.
4. Run `pnpm install` to install Node.js dependencies, which is useful for type checking.
5. Start the database, Redis, and PHP runtime for development:
   ```bash
   docker compose up -d
   ```
6. Create an administrator user by running:
   ```bash
   php ./bin/console app:create-user -p "yourpassword" -r "ROLE_ADMIN" "admin" "admin@youremail.tld"
   ```
7. (Optional) Import the schema and questions using:
   ```bash
   php ./bin/console app:import schema.json
   ```
8. Access the application at `https://localhost`, and use `https://localhost/admin` to access the admin panel.

## Deployment

### Zeabur

1. Deploy Redis, PostgreSQL, Meilisearch, and Umami (for statistics) on Zeabur.
2. Deploy the application in Git mode on Zeabur.
3. Add the following environment variables to the application:
   ```env
   DATABASE_URL=postgresql://${POSTGRES_USERNAME}:${POSTGRES_PASSWORD}@postgresql.zeabur.internal:5432/${POSTGRES_DATABASE}?serverVersion=16&charset=utf8
   SERVER_NAME=:${PORT}
   APP_SECRET=${PASSWORD}
   MEILISEARCH_URL=http://meilisearch.zeabur.internal:7700
   MEILISEARCH_API_KEY=${MEILI_MASTER_KEY}
   UMAMI_DOMAIN=your-umami-domain.tld
   UMAMI_WEBSITE_ID=your-website-id
   OPENAI_API_KEY=your-openai-api-key
   LINE_NOTIFY_DSN=linenotify://line-notify-token@default
   ```
4. Create an index in Meilisearch by running:
   ```bash
   php bin/console meili:create --update-settings
   ```
5. Bind your domain, and the application will be ready for use.

### Docker

We provide a Docker Compose configuration based on [Symfony Docker](https://github.com/dunglas/symfony-docker) for
deployment. The prebuilt image is available at
the [GitHub Registry](https://github.com/database-playground/app-sf/pkgs/container/app-sf).

To deploy the application, you may need to update the secret or environment variables in the `compose.yaml` and
`compose.prod.yaml` files, and then run the following command:

```bash
docker compose -f compose.yaml -f compose.prod.yaml up -d
```
