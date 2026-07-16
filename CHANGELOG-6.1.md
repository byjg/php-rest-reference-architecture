# Changelog - Version 6.1

## Overview

Version 6.1 is a security and maintenance release. The main driver is an upstream security advisory in `firebase/php-jwt` that affects all v6 releases of that library. Upgrading to the patched version enforces a stricter minimum key size for HMAC algorithms, which requires all `JWT_SECRET` values to be regenerated in the correct format. Documentation has also been restructured and improved across the board.

## Security Fix

### firebase/php-jwt — PKSA-y2cr-5h3j-g3ys (CVE-2025-45769, published 2025-07-31)

`firebase/php-jwt` had a known security advisory (**PKSA-y2cr-5h3j-g3ys** / CVE-2025-45769, published 2025-07-31) affecting all v6 releases. This version upgrades `byjg/jwt-wrapper` to a release that depends on the patched `firebase/php-jwt`, eliminating the vulnerability.

As a side effect of the fix, the library now strictly enforces a **minimum key size of 512 bits (64 bytes)** for HS512 — the default algorithm used by this architecture. Short plain-text secrets that were silently accepted before will now throw a `"Provided key is too short"` exception at login time.

**Action required:** all environments must have their `JWT_SECRET` regenerated. See the upgrade path below.

## Breaking Changes

| Before | After | Description |
|--------|-------|-------------|
| `JWT_SECRET=short-plain-text` | `JWT_SECRET=<base64 of 64+ bytes>` | `JWT_SECRET` must now be a base64-encoded string whose decoded value is at least 64 bytes. Plain-text secrets are rejected at runtime. |
| Existing JWT tokens | Invalidated | All tokens signed with the old secret become invalid after the secret is rotated. Users will need to log in again. |

## Changes

### JWT Secret Format Requirement

`JwtHashHmacSecret` base64-decodes the `JWT_SECRET` value before using it as the signing key. The patched `firebase/php-jwt` now validates that the decoded key is at least 512 bits (64 bytes) for HS512. Secrets must be generated with:

```bash
APP_ENV=dev composer terminal
php> \ByJG\JwtWrapper\JwtWrapper::generateSecret(64)
# => 'OFbOmC2VxlgQHNrBLa/wyj7/fFkgPnLpckbXMVuIU7Sqb3RTztNx3xzEYaoeA31JUpvBjkD7FRKBFGQ0+fnTig=='
```

> Note: `php -r` cannot be used directly because it does not load the project autoloader. Use `composer terminal` instead.

The `staging` and `prod` `credentials.env` templates now ship with a placeholder that already satisfies the minimum size and include generation instructions as comments.

`composer create-project` (PostCreateScript) already calls `JwtWrapper::generateSecret(64)` to produce a fresh compliant secret for every environment during setup — no changes needed there.

### Documentation

- Restructured documentation for improved clarity and navigation
- Updated all `JWT_SECRET` examples to use valid base64-encoded values
- Corrected minimum key size references from "32 characters" to "64 decoded bytes"
- Replaced `php -r` generation examples with the correct `composer terminal` command
- Removed redundant links in advanced configuration documentation
- Updated README badge and license link

## Upgrade Path from 6.0 to 6.1

### Step 1: Update Dependencies

```bash
composer update
```

### Step 2: Regenerate JWT_SECRET for Every Environment

Run the following once per environment and copy the output into the corresponding `config/<env>/credentials.env`:

```bash
APP_ENV=dev composer terminal
php> \ByJG\JwtWrapper\JwtWrapper::generateSecret(64)
```

Update each file:

```ini
# config/dev/credentials.env
JWT_SECRET=<output from above>

# config/test/credentials.env
JWT_SECRET=<output from above>

# config/staging/credentials.env
JWT_SECRET=<output from above>

# config/prod/credentials.env
JWT_SECRET=<output from above>
```

> Generate a **different** secret for each environment. Never reuse the same secret across environments.

### Step 3: Inform Users

All existing JWT tokens are invalidated once the secret is rotated. Users will receive a 401 on their next request and will need to log in again.

### Step 4: Run Tests

```bash
composer test
```

## Resources

- [JWT Advanced Guide](docs/guides/jwt-advanced.md)
- [Authentication Guide](docs/guides/authentication.md)
- [Getting Started Guide](docs/getting-started/installation.md)
- [Changelog 6.0](CHANGELOG-6.0.md)