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

We have 2 Zeabur template file:

- `zeabur/app.yaml`: The main application.
- `zeabur/monitor.yaml`: The uptime monitor (containing the worker monitors).

To deploy the application on Zeabur, follow these steps:

1. Update the `<VARIABLES>` in the template file. You would need to fill some tokens and secrets there.
2. Create 2 projects (can be on different regions) on Zeabur.
3. Run `npx zeabur template deploy -f zeabur/app.yaml --project-id PROJECT-ID` to deploy the application.
4. Run `npx zeabur template deploy -f zeabur/monitor.yaml --project-id PROJECT-ID` to deploy the uptime monitor.
5. Restore the backup of PostgreSQL or import the schema and questions using the `app:import` command.
6. Create an account if you haven't done so: `php ./bin/console app:create-user -p "yourpassword" -r "ROLE_ADMIN" "admin" "admin@youremail.tld"`
7. Set up your Uptime Kuma in the monitor project.

### Docker

We provide a Docker Compose configuration based on [Symfony Docker](https://github.com/dunglas/symfony-docker) for deployment. The prebuilt image is available at the [GitHub Registry](https://github.com/orgs/database-playground/packages).

To deploy the application, you may need to update the secret or environment variables in the `compose.yaml` and `compose.prod.yaml` files, and then run the following command:

```bash
export IMAGES_PREFIX=ghcr.io/database-playground/
docker compose -f compose.yaml -f compose.prod.yaml up -d
```
