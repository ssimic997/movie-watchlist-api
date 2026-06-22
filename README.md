# Movie Watchlist API

A REST API that lets authenticated users manage a personal movie watchlist. When a user adds a movie, the API fetches additional details from the OMDb external API and stores them locally, asynchronously via a queued job.

---

## Tech Stack

| Tool | Version |
|---|---|
| PHP | 8.3 |
| Composer | 2.8.x |
| Laravel | 13.7 |
| Database | PostgreSQL 15.5 |
| Queue / Cache | Redis |

---

## External API

This project uses the **OMDb API** (Open Movie Database).

- Register for a free API key at [omdbapi.com](https://www.omdbapi.com/apikey.aspx).
- The free tier allows up to 1,000 daily requests.
- Once you have a key, set it in `.env`:

```env
OMDB_API_KEY=your_key_here
```

---

## Docker

Docker is used **only for infrastructure** — PostgreSQL and Redis. The Laravel application itself runs via the built-in PHP dev server (no application container).

Start the infrastructure:

```bash
docker compose up -d
```

This brings up:
- **PostgreSQL 15.5** on port `5432` (configurable via `DB_PORT` in `.env`)
- **Redis** on port `6379` (configurable via `REDIS_PORT` in `.env`)

---

## Setup & Running

### 1. Clone and install dependencies

```bash
git clone https://github.com/ssimic997/movie-watchlist-api
cd movie-watchlist-api
composer install
```

### 2. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Open `.env` and configure the database, Redis, and OMDb API key:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=movie_watchlist
DB_USERNAME=your_user
DB_PASSWORD=your_password

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_STORE=redis

OMDB_API_KEY=your_key_here
```

### 3. Start infrastructure

```bash
docker compose up -d
```

### 4. Run migrations

```bash
php artisan migrate
```

### 5. Seed the database

The seeder creates two test users (both with password `password`):

| Email | Name |
|---|---|
| `test@example.com` | Test User |
| `test-postman@example.com` | Test Postman User |

```bash
php artisan db:seed
```

### 6. Start the queue worker

Movie metadata is fetched asynchronously. The queue worker must be running for metadata to appear after adding a movie:

```bash
php artisan queue:work --queue=fetch-movie-metadata
```

### 7. Start the application

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`.

---

## API Endpoints

All watchlist endpoints require a `Bearer` token in the `Authorization` header, obtained via login.

### Authentication

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `/api/auth/register` | Register a new user |
| `POST` | `/api/auth/login` | Login and receive a token |
| `DELETE` | `/api/auth/logout` | Revoke the current token |

### Watchlist

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/api/watchlist/movies` | List movies with filtering and pagination |
| `POST` | `/api/watchlist/movies` | Add a movie (by IMDb ID or title) |
| `GET` | `/api/watchlist/movies/{id}` | Get a single watchlist entry |
| `PATCH` | `/api/watchlist/movies/{id}` | Update user-specific fields |
| `DELETE` | `/api/watchlist/movies/{id}` | Remove a movie from the watchlist |

**`POST /api/watchlist/movies` — request body (one of the two identifiers is required):**

| Field | Type | Description |
|---|---|---|
| `external_id` | string | IMDb ID (e.g. `tt0111161`) — preferred; skips title search |
| `title` | string | Movie title — used when IMDb ID is not known |

**`PATCH /api/watchlist/movies/{id}` — all fields are optional:**

| Field | Type | Description |
|---|---|---|
| `status` | `to_watch`, `watching`, `watched` | Watch progress |
| `user_rating` | integer, 1–10 | Personal rating |

**Filtering & pagination parameters for `GET /api/watchlist/movies`:**

| Parameter | Type | Description |
|---|---|---|
| `status` | `to_watch`, `watching`, `watched` | Filter by watch status |
| `search` | string | Search by movie title (case-insensitive) |
| `sort_by` | `title`, `status` | Sort field |
| `sort_dir` | `asc`, `desc` | Sort direction |
| `per_page` | integer | Results per page (default: 5, max: 100) |

### Testing with Postman

A Postman collection is included in the repository. Import it directly into Postman to test all endpoints with pre-configured requests.

```
docs/movie-watchlist.postman_collection.json
```

---

## Authentication Approach

**Laravel Sanctum** with token-based authentication (SPA tokens issued as plain-text Bearer tokens).

Sanctum was chosen because:
- It is purpose-built for Laravel API authentication with zero extra infrastructure (no OAuth server required).
- Laravel's first-party, officially recommended package for API token authentication — the de facto standard in the Laravel ecosystem, with mature documentation and strong community support.
- Token issuance, scoping, and revocation are handled out of the box.
- On login, any existing tokens for the user are revoked before issuing a new one — ensuring a clean single-session behaviour without needing token expiry logic.

---

## Architecture: N-Layer Structure

The application follows a layered architecture where each layer has a single, well-defined responsibility. Dependencies always flow inward — outer layers depend on inner abstractions, never the reverse.

```
HTTP Layer  →  Service Layer  →  Repository Layer  →  Eloquent / Database
                     ↓
             External API Layer
                     ↓
               Queue / Jobs
```

### Layer breakdown

**HTTP Layer** (`app/Http/`)

Controllers, Form Requests, and API Resources. Controllers are intentionally thin — they validate input (delegated to Form Requests), call a single service method, and return a shaped response (delegated to API Resources). No business logic lives here.

**Service Layer** (`app/Services/`)

Contains all business logic. Services orchestrate operations across repositories and external providers:
- `AuthService` — login, logout, and registration token management.
- `WatchlistMovieService` — add, list, update, and remove movies. Handles the duplication check, the DB transaction, and fires the `MovieAddedToWatchlist` event after a successful add.
- `MovieService` — triggers a metadata re-fetch for an existing movie.

**Repository Layer** (`app/Repositories/`, `app/Contracts/`)

Abstracts all database interaction behind interfaces (`WatchlistRepositoryContract`, `MovieRepositoryContract`, `UserRepositoryContract`). Services depend on contracts, not concrete classes. The concrete implementations are bound in `RepositoryProvider`. This means the database layer can be swapped or mocked in tests without touching service code.

**External API Layer** (`app/Services/MovieApi/`, `app/Contracts/MovieApiProviderContract.php`)

See the [Multi-Provider Design](#multi-provider-design) section below.

**Event / Job Layer** (`app/Events/`, `app/Listeners/`, `app/Jobs/`)

Metadata fetching is decoupled from the add flow via an event:
1. `WatchlistMovieService` dispatches `MovieAddedToWatchlist` after the transaction commits.
2. `DispatchFetchMetadataJob` listens and pushes `FetchMovieMetadataJob` onto the `fetch-movie-metadata` queue.
3. The job calls the provider, maps the result, and persists the metadata. It retries up to 3 times on network errors. A provider "not found" response records a `FAILED` status without retrying, so failed_jobs stays clean.

`FetchMovieMetadataJob` implements `ShouldBeUnique` with a unique ID of `{movie_id}:{provider}`. If multiple users add the same movie concurrently, only one external API request is made — subsequent dispatches for the same movie/provider pair are dropped by the queue until the first job completes.

All metadata fetch jobs are placed on a dedicated `fetch-movie-metadata` queue rather than the default queue. This allows the queue worker for external API calls to be configured, scaled, and throttled independently — for example: a separate worker process with a lower concurrency limit (to respect provider rate limits), a different retry backoff, or priority tuning without affecting other background work.

---

## Model Structure

The data model separates movie identity from user-specific state and from provider-sourced metadata. This allows the same movie record to be shared across multiple users' watchlists while keeping metadata per-provider.

```
User
 └── Watchlist (one per user, auto-created on first use)
      └── WatchlistMovie (pivot: status, user_rating)
           └── Movie
                ├── MovieExternalId  (one per provider — stores the IMDb ID etc.)
                └── MovieMetadata    (one per provider — stores enriched details)
```

### Models

**`User`**

Standard Laravel user with Sanctum token support. Has one `Watchlist` (created lazily on first request).

**`Watchlist`**

Belongs to a `User`. Holds the many-to-many relationship to `Movie` through the `watchlist_movies` pivot table.

**`WatchlistMovie`** (Pivot)

The join between `Watchlist` and `Movie`. Carries user-specific state:
- `status` — enum: `to_watch`, `watching`, `watched`
- `user_rating` — optional personal rating

**`Movie`**

The canonical movie record. Stores only the `title` (which may be updated once the external API responds with the canonical name). Is shared across all watchlists — two users adding the same IMDb ID reuse the same `Movie` row.

**`MovieExternalId`**

Maps a `Movie` to its identifier in a given external provider (e.g. `provider = "omdb"`, `external_id = "tt0111161"`). One record per provider per movie. Used by the job to know which ID to pass to the API on a re-fetch.

**`MovieMetadata`**

Stores enriched data returned by an external provider for a given movie. One record per provider per movie. Fields:

| Field | Description |
|---|---|
| `provider` | Provider name (e.g. `omdb`) |
| `metadata_status` | `pending`, `successful`, or `failed` |
| `year` | Release year |
| `rated` | Content rating (e.g. PG-13) |
| `runtime` | Runtime string |
| `genre` | Genre string |
| `director` | Director name(s) |
| `writer` | Writer name(s) |
| `actors` | Actor names |
| `plot` | Short plot summary |
| `poster_url` | Poster image URL |
| `provider_rating` | Rating from the provider (e.g. IMDb score) |
| `raw_response` | Full raw JSON from the API, stored as JSONB |

---

## Multi-Provider Design

The external API integration is designed to accommodate multiple providers without requiring changes to the core application logic.

### Current: single active provider (swap model)

`MovieApiProviderContract` defines the three methods every provider must implement:
- `findByExternalId(string $externalId): MovieApiResult`
- `searchByTitle(string $title): MovieApiResult`
- `providerName(): string`

`MovieApiResult` is a normalized DTO — every provider maps its own response shape into this common structure, acting as an **Adapter**. `OmdbMovieApiProvider` is the current implementation; swapping to a different provider (e.g. TMDB) means writing a new class implementing the contract and changing the single binding in `AppServiceProvider`:

```php
$this->app->bind(MovieApiProviderContract::class, TmdbMovieApiProvider::class);
```

No other code changes are needed. The DB schema is already multi-provider ready — `MovieMetadata` and `MovieExternalId` both carry a `provider` column, so a single `Movie` row can accumulate metadata from multiple providers in parallel, each in its own row.

### Evolving to multiple simultaneous providers

When the requirement is to fetch from *all* registered providers for every movie (not just one active at a time), three design changes are needed:

**1. Provider Registry (Strategy pattern)**

Replace the single contract binding with a named registry:

```php
// AppServiceProvider
$this->app->singleton(MovieApiProviderRegistry::class, function () {
    return new MovieApiProviderRegistry([
        'omdb' => $this->app->make(OmdbMovieApiProvider::class),
        'tmdb' => $this->app->make(TmdbMovieApiProvider::class),
    ]);
});
```

`FetchMovieMetadataJob` already carries `$providerName` and resolves the right provider by name from the registry rather than asking the container for whatever is bound to the contract. The unique ID (`{movie_id}:{provider}`) and the `MovieMetadata`/`MovieExternalId` `provider` columns require no changes.

**2. Fan-out dispatch**

`DispatchFetchMetadataJob` (the event listener) dispatches one `FetchMovieMetadataJob` per enabled provider instead of one. Each job runs independently, writes its own `MovieMetadata` row, and is deduplicated per-provider via `ShouldBeUnique`.

**3. Adapter per provider (already the pattern)**

Each provider class encapsulates the translation from its API's response format to `MovieApiResult`. If two providers return dates or runtime in different formats, the normalization stays inside the provider's `toResult()` method — the job and the DTO are untouched.

---

## Decisions & Trade-offs

**API shape**

URL structure: the watchlist routes are `watchlist/movies` with no watchlist ID in the path. Because each user has exactly one watchlist (auto-created on first use), exposing a watchlist ID would be redundant — there is nothing to select between.

Status codes are chosen to match HTTP semantics precisely:
- `201 Created` — movie added to watchlist
- `200 OK` — successful read or update
- `204 No Content` — movie removed (no body to return)
- `404 Not Found` — movie not in the authenticated user's watchlist
- `409 Conflict` — movie already in the watchlist

User-specific watchlist field beyond `status`: `user_rating` (integer 1–10) — a personal score that covers the primary annotation a user would want without over-engineering a structured tagging or review system.

**Async metadata fetching**

Metadata is fetched in a queued job after the movie is added, not inline in the HTTP request. This keeps the `POST /watchlist/movies` response fast (no external HTTP call on the critical path) and makes the external dependency failure-tolerant — network errors are retried, permanent failures are recorded without polluting `failed_jobs`.

**One watchlist per user**

The assignment scopes everything to "a personal movie watchlist". Rather than requiring users to create a watchlist explicitly before adding movies, the API auto-creates a default watchlist on first use (`firstOrCreate`). The model supports multiple watchlists per user if that requirement ever arrives.

**ULID primary keys**

All models use ULIDs instead of auto-incrementing integers. ULIDs are URL-safe, globally unique, and sortable by creation time — a good default for API-exposed identifiers.

**No soft deletes**

Removing a movie from a watchlist detaches the pivot row. The `Movie` record is not deleted — it may belong to other users' watchlists, and its metadata is shared. This avoids orphan metadata and keeps re-add cheap (the external API call is skipped if metadata already exists for the provider).

**Shared movie records with deduped metadata fetching**

Movies are shared across users — two users adding the same IMDb ID reuse the same `Movie` row and the same `MovieMetadata` row. The `WatchlistMovieService` checks whether metadata already exists for the provider before dispatching a job, so repeat adds by different users never trigger redundant API calls. Combined with the `ShouldBeUnique` constraint on the job, concurrent adds of the same movie result in exactly one external API request.

**What was skipped**

- No exhaustive test suite. The focus was on a clean, testable architecture where services depend on interfaces rather than concrete implementations, making unit testing straightforward.
- No Docker container for the app itself — only infrastructure services are containerised.

---

## Known Limitations & Future Improvements

**API versioning**

The API is served under `/api` with no version segment. The preferred approach would be content negotiation via the `Accept` header (e.g. `Accept: application/vnd.movie-watchlist.v1+json`), which keeps URLs stable as the contract evolves — clients opt into a new version without any URL change. A simpler fallback is a URI prefix (`/api/v1`).

**Service contracts**

The repository and movie API provider layers are bound to interfaces (`app/Contracts/`), but the three service classes (`AuthService`, `MovieService`, `WatchlistMovieService`) are injected as concrete classes. Extracting service interfaces would make them mockable in unit tests without a real DB or HTTP client, and would mirror the consistency of the repository layer. Left out to avoid over-abstraction for a task of this scope.

**Caching on read paths**

`GET /watchlist/movies` and `GET /watchlist/movies/{id}` hit the database on every request — there is no caching layer. A per-user Redis read-through cache (keyed on user ID + query params), invalidated on add/update/remove, would eliminate most DB reads for read-heavy usage. Redis is already provisioned via Docker, so adding this is low-friction.

**Rate limiting**

Only the login endpoint is throttled (5 requests/minute via Fortify). The watchlist and movie endpoints in `routes/api.php` have no `throttle` middleware. A production deployment would add `throttle:api` (per-user/IP) across all authenticated routes, and tighter limits on `POST /watchlist/movies` specifically, since each add can fan out to a queued external API call.

**Access token expiry & no refresh token**

Sanctum token expiration is set to `null` — tokens never expire and are valid until explicitly revoked. Login revokes all prior tokens before issuing a new one; logout revokes the current token. This is deliberate: Sanctum personal access tokens have no refresh-token semantics, so introducing expiry without a refresh flow would force clients to re-authenticate silently or show login prompts on expiry. For a task of this scope, explicit revocation is sufficient. The next step would be setting a finite `expiration` in `config/sanctum.php` alongside a `POST /auth/refresh` endpoint.

**Metadata status: `failed` vs `not_found`**

When the external API returns no match for a title or IMDb ID, `FetchMovieMetadataJob` records `metadata_status = failed`. Unlimited refetching is intentional — if a user misspells a title, they can update it via `PATCH /watchlist/movies/{id}` and trigger a re-fetch, which will now succeed. The issue is UX: `failed` implies a technical error, not a "no results" outcome. The recommended improvement is to introduce a distinct `not_found` status in the `MovieMetadataStatus` enum (alongside `pending`, `successful`, `failed`), used only when the provider confirms no match exists. This lets the client display a clear "title not found — check the spelling and refetch" message, while `failed` remains reserved for genuine network or server errors.

**Wrong media type via IMDb ID**

When a movie is added by `external_id`, OMDb ignores the `type=movie` query parameter and returns whatever that IMDb ID resolves to — including TV series, mini-series, or episodes. The `Type` field in the OMDb response is validated; if it is not `movie`, `FetchMovieMetadataJob` records `metadata_status = wrong_media_type` (a permanent, unrecoverable outcome — no retry). This surfaces clearly to the client rather than silently storing series metadata against a movie entry.

**Watchlist filter coverage**

The list endpoint (`GET /watchlist/movies`) currently filters on `status` and `search` (title substring), and sorts by `status` or `title`. Several useful filters are absent:

- `user_rating` — stored in the `watchlist_movies` pivot; straightforward range filter (`min_rating`, `max_rating`).
- `genre`, `director`, `year`, `provider_rating` — stored in `movie_metadata`; require a join from the `watchlist_movies`/`movies` query onto `movie_metadata` (filtering on the row where `provider = current_provider`). Genre is a comma-separated string from OMDb, so filtering would need `ILIKE '%Action%'` rather than an exact match.
- Sort by `year`, `user_rating`, or `provider_rating` — same join dependency as the metadata filters.

The metadata join is straightforward but needs care: a movie whose metadata fetch has not yet completed (`metadata_status = pending`) would be excluded by an inner join, so a `LEFT JOIN` with null-safe filter conditions is required to keep in-flight movies visible.

**Provider API error handling & rate limits**

OMDb allows 1,000 requests per 24 hours on the free tier. When that limit is exceeded, OMDb returns HTTP 200 with a JSON body of `{"Response":"False","Error":"Request limit reached!"}` — not an HTTP 4xx/5xx. The current provider (`OmdbMovieApiProvider`) only calls `->throw()`, which catches HTTP-level failures. A rate-limit response slips past `->throw()`, hits the `Response: False` check, and is treated identically to "movie not found" — recording `metadata_status = failed` (or `not_found` after the rename above) when the real cause is an exhausted quota.

Improvements to consider:

1. **Distinguish error types in the provider.** After the `Response: False` check, inspect `$response['Error']` and throw a dedicated `ProviderRateLimitException` (vs `MovieNotFoundException`). The job can then catch it separately.
2. **Delay rather than fail on rate limit.** The `ProviderRateLimitException` path in the job should `$this->release(seconds: 3600)` — re-queue the job for one hour later — rather than recording a permanent failure. Laravel's `ShouldBeUnique` lock must be released explicitly in this path so the re-queued job is not blocked.
3. **Request budget tracking.** A Redis counter (`INCR omdb:requests:<date>`, TTL 24 h) incremented on each provider call lets the application detect when the quota is nearly exhausted and hold new jobs before they waste a request.
4. **Circuit breaker.** If the rate-limit error is encountered, skip dispatching further metadata jobs for the remainder of the day rather than queuing hundreds that will all be released one by one.
