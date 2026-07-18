export const BASE_URL = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8080';

function decodeJwtPayload(t) {
  try {
    const base64 = t.split('.')[1].replace(/-/g, '+').replace(/_/g, '/');
    return JSON.parse(atob(base64));
  } catch {
    return null;
  }
}

export const token = {
  get: () => sessionStorage.getItem('jwt_token'),
  set: (t) => sessionStorage.setItem('jwt_token', t),
  clear: () => sessionStorage.removeItem('jwt_token'),
  isExpired: () => {
    const t = sessionStorage.getItem('jwt_token');
    if (!t) return true;
    const payload = decodeJwtPayload(t);
    if (!payload?.exp) return true;
    return payload.exp < Date.now() / 1000;
  },
  expiresInSec: () => {
    const t = sessionStorage.getItem('jwt_token');
    if (!t) return 0;
    const payload = decodeJwtPayload(t);
    if (!payload?.exp) return 0;
    return payload.exp - Date.now() / 1000;
  },
};

// Injected once at startup (see App) so the requester can surface cross-cutting
// auth failures (expired session / insufficient role) without every page repeating it.
let onAuthError = null;
export function setAuthErrorHandler(fn) {
  onAuthError = fn;
}

async function apiFetch(path, options = {}) {
  const headers = { 'Content-Type': 'application/json', ...(options.headers || {}) };
  let authenticated = false;

  const currentToken = token.get();
  if (currentToken && !token.isExpired()) {
    // Transparently refresh when less than 5 minutes remain.
    if (token.expiresInSec() < 300) {
      try {
        const refreshRes = await fetch(`${BASE_URL}/refreshtoken`, {
          method: 'POST',
          headers: { Authorization: `Bearer ${currentToken}`, 'Content-Type': 'application/json' },
        });
        if (refreshRes.ok) {
          const data = await refreshRes.json();
          if (data.token) token.set(data.token);
        }
      } catch {
        // Ignore refresh errors; proceed with the current token.
      }
    }
    headers['Authorization'] = `Bearer ${token.get()}`;
    authenticated = true;
  }

  const res = await fetch(`${BASE_URL}${path}`, { ...options, headers });

  // Centralised auth-error capture. 403 always means "insufficient role";
  // 401 on an authenticated request means the session expired (a 401 on the
  // login/refresh calls themselves has no token attached and is left to the caller).
  if (onAuthError && !options.skipAuthError) {
    if (res.status === 403) onAuthError(403, res);
    else if (res.status === 401 && authenticated) onAuthError(401, res);
  }

  return res;
}

export const api = {
  post: (path, body, opts) => apiFetch(path, { method: 'POST', body: JSON.stringify(body), ...opts }),
  get: (path, opts) => apiFetch(path, { method: 'GET', ...opts }),
  put: (path, body, opts) => apiFetch(path, { method: 'PUT', body: JSON.stringify(body), ...opts }),
  del: (path, opts) => apiFetch(path, { method: 'DELETE', ...opts }),
};
