---
sidebar_position: 190
title: Frontend (Vite)
---

# Frontend (Vite + React)

A Gluo project can ship an optional single-page frontend in the `html/` folder. It is a
**React 19 + Vite 6 + Tailwind** app that talks to the API over JWT, with login,
password-reset, dashboard and profile screens already wired.

The frontend is installed when you answer **yes** to *Install Frontend* during
`composer create-project` (or set `"install_frontend": true` in `setup.json`). Answer no
and the `html/` folder, its Docker image and the `html` compose service are all removed —
leaving a pure API project.

## Layout

```
html/
├── package.json  vite.config.js  tailwind.config.js
├── .env.example                     # VITE_API_BASE_URL
└── src/
    ├── lib/api.js                   # fetch wrapper: JWT storage + auto-refresh
    ├── context/AuthContext.jsx      # auth state (sessionStorage)
    ├── components/                   # AppNav + small UI primitives
    └── pages/
        ├── auth/Login.jsx
        ├── auth/ForgotPassword.jsx   # 3-step reset flow
        ├── dashboard/Dashboard.jsx
        ├── dashboard/Profile.jsx     # GET/PUT /profile
        └── examples/                 # gated by install_examples
            ├── ProjectsList.jsx      ProjectDetail.jsx   NotesWidget.jsx
```

## Local development

```bash
cd html
cp .env.example .env      # point VITE_API_BASE_URL at your API (default http://localhost:8080)
npm install
npm run dev               # Vite dev server on http://localhost:3000
```

The API's dev CORS allowlist (`CORS_SERVERS` in `api/config/dev/credentials.env`) already
permits the Vite origin. In **production**, set `CORS_SERVERS` to the exact origin the SPA
is served from.

## How auth works

- `src/lib/api.js` keeps the JWT in `sessionStorage` and attaches `Authorization: Bearer …`
  to every request. When fewer than 5 minutes remain it transparently calls
  `POST /refreshtoken` and swaps the token.
- `AuthContext` exposes `login/logout/isAuthenticated`; protected routes redirect to
  `/login` when the token is missing or expired.

The screens map onto the API endpoints: `POST /login`, `POST /refreshtoken`,
`POST /login/resetrequest` → `/login/confirmcode` → `/login/resetpassword`, and
`GET`/`PUT /profile`.

## Running with Docker Compose

`docker compose up -d` builds `docker/Dockerfile-html`: a Node stage runs `npm run build`,
then the static `dist/` output is served by [`byjg/static-httpserver`](https://github.com/byjg/docker-static-httpserver)
in SPA mode. The SPA is published on **http://localhost:7080** (the API stays on
`:8080`).

> `VITE_API_BASE_URL` is baked in at **build** time. For a production image, build with the
> real API URL (e.g. `--build-arg` or an `.env` committed to your deploy pipeline).

## Removing the example screens

The Projects/Tasks/Notes pages under `src/pages/examples/` are removed when
`install_examples=false`; their imports, routes and nav entries are wrapped in
`{/* >>> examples */}` … `{/* <<< examples */}` markers so the generator can strip them
cleanly, leaving just the auth + dashboard + profile shell.

## Not included

Public self-registration is intentionally out of scope for the starter shell (the API has
no `/register` endpoint by default). Add it as your first real feature — a controller plus
the matching frontend page — following the [REST Controllers](rest-controllers.md) guide.
