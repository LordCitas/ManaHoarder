# ManaHoarder

- Secciones mainboard/sideboard y export/import.
- Autocompletado de cartas.
- Integración con API (Scryfall) usando `Card.externalId` + `Card.imageUrl`.
## Próximos pasos (idea)

- Dos vistas en una lista: grid (con placeholder de imagen) y compacta (nombre + cantidad).
- Añadir/eliminar cartas por nombre (por ahora se crean con placeholders).
- CRUD de listas (`/lists`).
- Registro/login de usuarios.
## Funcionalidades actuales (MVP)

   - `symfony serve -d` (si tienes Symfony CLI) o `php -S 127.0.0.1:8000 -t public`
4. Arranca Symfony:
   - `php bin/console doctrine:migrations:migrate`
3. Ejecuta migraciones:

> Nota: en tu Windows puede que Docker publique la DB en un puerto distinto al 5432. Mira `docker compose ps` y ajusta el puerto en `.env.local`.

   - `DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=16&charset=utf8"`
2. Asegura tu conexión local en `.env.local` (no se commitea). Ejemplo:
   - `docker compose up -d`
1. Levanta servicios (DB + mailer):
## Puesta en marcha (desarrollo)

- Docker Desktop (para PostgreSQL vía `compose.yaml`)
- Composer
- PHP (según tu proyecto, actualmente funciona con PHP 8.4)
## Requisitos

App Symfony para crear y gestionar listas de cartas de *Magic: the Gathering*.

