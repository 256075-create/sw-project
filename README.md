# University Management System (UMS)

A production-ready University Management System built with Laravel 11 (Modular Monolith) and React + TypeScript.

## Architecture

**Backend**: Laravel 11 with modular monolith architecture
**Frontend**: React + TypeScript + Vite + Tailwind CSS v4
**Database**: MySQL 8.0 with prefixed tables per module
**Cache/Queue**: Redis
**Search**: Elasticsearch 8.x
**Auth**: JWT (HS256) with access/refresh token flow

## Module Structure

```
app/Modules/
├── Identity/        # Authentication, Authorization, User/Role/Permission management
├── Academic/        # University → College → Department → Major hierarchy
├── Registration/    # Courses, Sections, Classrooms, Schedules
└── Student/         # Student records, Enrollment, Timetable
```

### Identity Module
- JWT Authentication (15-min access token, 24-hr refresh token)
- RBAC with 3 roles: Admin, Registration Staff, Student
- Permission-based API access control
- User management (CRUD, activate/deactivate)

### Academic Module
- Full university hierarchy: University → College → Department → Major
- Hierarchy view endpoint
- CRUD for all entities

### Registration Module
- Course management with activation/deactivation
- Section management with capacity tracking
- Classroom management
- Schedule management with conflict detection

### Student Module
- Student record management with auto-generated student numbers
- Enrollment with validation (capacity, duplicates, schedule conflicts)
- Drop enrollment with automatic section count adjustment
- Weekly timetable generation

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend Framework | Laravel 11.x |
| Frontend Framework | React 18 + TypeScript |
| Build Tool | Vite |
| CSS | Tailwind CSS v4 |
| State Management | Zustand |
| Data Fetching | TanStack React Query |
| HTTP Client | Axios |
| Database | MySQL 8.0 |
| Cache | Redis |
| Search | Elasticsearch 8.x |
| Auth | JWT (firebase/php-jwt) |
| Container | Docker + Docker Compose |
| Logging | ELK Stack (Elasticsearch, Logstash, Kibana) |

## Getting Started

### Prerequisites
- PHP 8.4+
- Composer
- Node.js 21+
- MySQL 8.0
- Redis
- Elasticsearch 8.x (optional)

### Installation

```bash
# Clone and install backend
cd ums
composer install
cp .env.example .env
php artisan key:generate

# Configure database in .env, then:
php artisan migrate
php artisan db:seed

# Install and run frontend
cd frontend
npm install
npm run dev
```

### Docker Setup

```bash
# Start all services
docker compose up -d

# Stop all services
docker compose down
```

Services:
- **App** (PHP-FPM): port 9000
- **Nginx** (API): port 8000
- **Frontend** (React): port 3000
- **MySQL**: port 3308
- **Redis**: port 6379
- **Elasticsearch**: port 9200
- **Logstash**: port 5044
- **Kibana**: port 5601

The `entrypoint.sh` script automatically handles: composer install, migrations, seeding (if empty DB), and cache clearing on first run.

### Running Tests

```bash
php artisan test
# or
./vendor/bin/phpunit
```

## API Endpoints

### Authentication
| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/login` | No | Login |
| POST | `/api/auth/refresh` | No | Refresh token |
| POST | `/api/auth/logout` | Yes | Logout |
| GET | `/api/me` | Yes | Current user |

### Academic
| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET | `/api/academic/hierarchy` | academic.read | Full hierarchy |
| GET/POST | `/api/universities` | academic.read/create | List/Create |
| GET/PUT/DELETE | `/api/universities/{id}` | academic.* | Show/Update/Delete |
| GET/POST | `/api/colleges` | academic.read/create | List/Create |
| GET/POST | `/api/departments` | academic.read/create | List/Create |
| GET/POST | `/api/majors` | academic.read/create | List/Create |

### Registration
| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET/POST | `/api/courses` | courses.read/create | List/Create courses |
| GET/PUT/DELETE | `/api/courses/{id}` | courses.* | CRUD |
| POST | `/api/courses/{id}/activate` | courses.update | Activate |
| POST | `/api/courses/{id}/deactivate` | courses.update | Deactivate |
| GET/POST | `/api/sections` | sections.read/create | List/Create |
| GET/PUT/DELETE | `/api/sections/{id}` | sections.* | CRUD |
| GET/POST | `/api/classrooms` | classrooms.read/create | List/Create |
| POST/PUT/DELETE | `/api/schedules` | sections.* | CRUD |

### Students
| Method | Endpoint | Permission | Description |
|--------|----------|-----------|-------------|
| GET/POST | `/api/students` | students.read/create | List/Create |
| GET/PUT/DELETE | `/api/students/{id}` | students.* | CRUD |
| GET | `/api/student/profile` | students.read | Current student |
| GET | `/api/students/{id}/enrollments` | enrollments.read | Enrollments |
| POST | `/api/enrollments` | enrollments.create | Enroll |
| POST | `/api/enrollments/{id}/drop` | enrollments.delete | Drop |
| GET | `/api/students/{id}/timetable` | timetable.read | Timetable |

## API Documentation (Swagger)

Interactive Swagger UI is available at `/api/docs` when the server is running.

## Default Credentials

After seeding:
- **Admin**: username: `admin`, password: `Admin@123`

## ELK Logging

Login/logout events are automatically indexed to Elasticsearch (`ums-auth-events` index). Application logs are forwarded via Logstash to `ums-laravel-logs-*` indices. View logs in Kibana at `http://localhost:5601`.

## Project Structure

```
ums/
├── app/
│   ├── Logging/                   # Custom log handlers (Elasticsearch)
│   ├── Modules/
│   │   ├── Identity/              # Auth & user management
│   │   ├── Academic/              # Academic hierarchy
│   │   ├── Registration/          # Course & section management
│   │   └── Student/               # Student & enrollment
│   ├── Providers/                 # App-level providers
│   └── Services/Search/           # Elasticsearch search services
├── config/                        # App + Elasticsearch config
├── database/seeders/              # Main DatabaseSeeder
├── docker/                        # Docker configs
├── frontend/                      # React + TypeScript SPA
│   └── src/
│       ├── api/                   # API client & endpoints
│       ├── components/            # React components
│       ├── hooks/                 # Custom hooks
│       ├── pages/                 # Page components
│       ├── store/                 # Zustand stores
│       ├── types/                 # TypeScript types
│       └── utils/                 # Utilities
├── public/docs/                   # Swagger UI + OpenAPI spec
└── docker-compose.yml             # Docker Compose (8 services)
```
