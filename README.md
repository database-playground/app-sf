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
   - `ROLE_USER` is the default role suitable for regular users, such as students.
   - `ROLE_ADMIN` is the administrator role that grants access to the admin panel, enables management of questions, and allows viewing of all submissions and feedback.
   - `ROLE_ALLOWED_TO_SWITCH` is the role that permits switching (impersonating) to another user, which is useful for debugging.
7. (Optional) Import the schema and questions using:
   ```bash
   php ./bin/console app:import schema.json
   ```
8. Access the application at `https://localhost`, and use `https://localhost/admin` to access the admin panel.

## Deployment

### Zeabur

1. Deploy Redis, PostgreSQL, Meilisearch, and Umami (for statistics) on Zeabur.
2. Deploy [SQL runner](https://github.com/database-playground/sqlrunner-v2) on Zeabur, and rename the service host to
   `sqlrunner`.
3. Deploy the application in Git mode on Zeabur.
4. Deploy the worker in Git mode on Zeabur. The service name should be `worker`.
5. Add the following environment variables to the application:
   ```env
   DATABASE_URL=postgresql://${POSTGRES_USERNAME}:${POSTGRES_PASSWORD}@postgresql.zeabur.internal:5432/${POSTGRES_DATABASE}?serverVersion=16&charset=utf8
   REDIS_URI=${REDIS_CONNECTION_STRING}
   SERVER_NAME=:${PORT}
   APP_SECRET=${PASSWORD}
   MEILISEARCH_URL=http://meilisearch.zeabur.internal:7700
   MEILISEARCH_API_KEY=${MEILI_MASTER_KEY}
   UMAMI_DOMAIN=your-umami-domain.tld
   UMAMI_WEBSITE_ID=your-website-id
   OPENAI_API_KEY=your-openai-api-key
   LINE_NOTIFY_DSN=linenotify://line-notify-token@default
   SQLRUNNER_URL=http://sqlrunner.zeabur.internal:8080
   ```
6. Add the following environment variables to the worker:
   ```env
   DATABASE_URL=postgresql://${POSTGRES_USERNAME}:${POSTGRES_PASSWORD}@postgresql.zeabur.internal:5432/${POSTGRES_DATABASE}?serverVersion=16&charset=utf8
   APP_SECRET=${PASSWORD}
   ```
7. Bind your domain, and the application will be ready for use. The Meilisearch index will be automatically created on start up.

### Docker

We provide a Docker Compose configuration based on [Symfony Docker](https://github.com/dunglas/symfony-docker) for
deployment. The prebuilt image is available at
the [GitHub Registry](https://github.com/database-playground/app-sf/pkgs/container/app-sf).

To deploy the application, you may need to update the secret or environment variables in the `compose.yaml` and
`compose.prod.yaml` files, and then run the following command:

```bash
docker compose -f compose.yaml -f compose.prod.yaml up -d
```
