---
sidebar_position: 15
---

# Unattended Setup Mode

The PostCreateScript supports unattended (non-interactive) setup via a JSON configuration file.

## How It Works

1. Place a `setup.json` file in one of the supported locations (see below)
2. Run `composer create-project`
3. The setup script will detect the file and run in unattended mode
4. The `setup.json` file remains for reuse across multiple projects

## Configuration File Locations

The script searches for `setup.json` in the following locations (in priority order):

### 1. Environment Variable (Highest Priority)

Use the `SETUP_JSON` environment variable to specify a custom location:

```bash
SETUP_JSON=/path/to/custom-setup.json composer -sdev create-project byjg/rest-reference-architecture my-project ^6.0
```

**Use cases:**
- Custom configuration locations
- CI/CD pipelines with specific configs
- Temporary overrides for specific projects

### 2. Parent Directory

The script looks in the directory where you run the `composer create-project` command:

```bash
/home/user/projects/setup.json          # ← Config file here
/home/user/projects/my-project/         # ← New project created here
```

**Use cases:**
- Project-specific configurations
- Quick one-off setups
- When you want config alongside projects

### 3. User Home Directory (Recommended for Personal Defaults)

The script checks your home directory:

**Linux/Mac:**
```bash
~/.rest-reference-architecture/setup.json
```

**Windows:**
```
C:\Users\YourName\.rest-reference-architecture\setup.json
```

**Use cases:**
- Personal default configurations
- Settings you want to reuse for all projects
- Keep your workspace clean

## Setup Examples

### Example 1: Personal Defaults in Home Directory

Create your personal defaults once:

```bash
# Linux/Mac
mkdir -p ~/.rest-reference-architecture
cat > ~/.rest-reference-architecture/setup.json << 'EOF'
{
  "git_user_name": "John Doe",
  "git_user_email": "john.doe@example.com",
  "install_examples": false
}
EOF

# Now create projects anywhere - they'll use your defaults
cd ~/projects
composer -sdev create-project byjg/rest-reference-architecture project1 ^6.0
composer -sdev create-project byjg/rest-reference-architecture project2 ^6.0
```

### Example 2: Parent Directory (Quick Setup)

```bash
# Create config in your projects directory
cd ~/projects
cat > setup.json << 'EOF'
{
  "namespace": "MyApp",
  "composer_name": "mycompany/myapp",
  "install_examples": false
}
EOF

# Create project in the same directory
composer -sdev create-project byjg/rest-reference-architecture my-project ^6.0
```

### Example 3: Environment Variable (CI/CD)

```bash
# Store config anywhere
cat > /etc/ci-configs/rest-setup.json << 'EOF'
{
  "git_user_name": "CI Bot",
  "git_user_email": "ci@company.com",
  "namespace": "AutoDeployApp",
  "install_examples": false
}
EOF

# Use it with environment variable
SETUP_JSON=/etc/ci-configs/rest-setup.json composer -sdev create-project byjg/rest-reference-architecture production-app ^6.0
```

## Configuration Options

| Field              | Type    | Default                                           | Description                                  |
|--------------------|---------|---------------------------------------------------|----------------------------------------------|
| `git_user_name`    | string  | Global git config or "Your Name"                  | Git user name for the project                |
| `git_user_email`   | string  | Global git config or "your.email@example.com"     | Git user email for the project               |
| `php_version`      | string  | Current PHP version                               | PHP version (8.1, 8.2, 8.3, 8.4)             |
| `namespace`        | string  | "MyRest"                                          | Project namespace (CamelCase)                |
| `composer_name`    | string  | "me/myrest"                                       | Composer package name (vendor/package)       |
| `mysql_connection` | string  | "mysql://root:mysqlp455w0rd@mysql-container/mydb" | MySQL connection string                      |
| `timezone`         | string  | "UTC"                                             | Server timezone                              |
| `install_examples` | boolean | true                                              | Include example code (Dummy, Sample classes) |

## All Fields Are Optional

If a field is not provided in `setup.json`, the default value will be used. You can provide only the fields you want to customize:

```json
{
  "namespace": "MyCustomApp",
  "composer_name": "company/custom-app",
  "install_examples": false
}
```

## Priority Order

When multiple `setup.json` files exist, the script uses the **first one found**:

1. ✅ `SETUP_JSON` environment variable → **Highest priority**
2. ✅ Parent directory `../setup.json`
3. ✅ Home directory `~/.rest-reference-architecture/setup.json`

**Example:** If you have both a home directory config and use `SETUP_JSON`, the environment variable wins.

## Docker Warning in Unattended Mode

If Docker is not installed, the warning will be displayed, but the setup will continue automatically without waiting for user input.

## Interactive Mode

If `setup.json` does not exist, the setup runs in interactive mode (default behavior) and prompts for all configuration values.

## Security Note

The `setup.json` file is listed in `.gitignore` to prevent accidentally committing it to version control. 
If it contains sensitive information, consider adding it to your global gitignore as well.
