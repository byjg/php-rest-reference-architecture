# Unattended Setup Mode

The PostCreateScript supports unattended (non-interactive) setup via a JSON configuration file.

## How It Works

1. Create a `setup.json` file in your **current directory** (where you'll run the command)
2. Run `composer create-project`
3. The setup script will detect the file and run in unattended mode
4. The `setup.json` file remains in your directory for reuse

## Usage

### Step 1: Create setup.json in your current directory

Create `setup.json` in the directory where you want to create projects:

```json
{
  "git_user_name": "John Doe",
  "git_user_email": "john.doe@example.com",
  "php_version": "8.4",
  "namespace": "MyApp",
  "composer_name": "mycompany/myapp",
  "mysql_connection": "mysql://root:password@mysql-container/mydb",
  "timezone": "America/New_York",
  "install_examples": false
}
```

### Step 2: Run composer create-project

```bash
# Run in the same directory where setup.json exists
composer create-project byjg/rest-reference-architecture my-project ^6.0
```

The setup will run automatically using your configuration.

**Note:** The `setup.json` file will remain in your directory, so you can reuse it for creating multiple projects with the same configuration.

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

## Docker Warning in Unattended Mode

If Docker is not installed, the warning will be displayed but the setup will continue automatically without waiting for user input.

## Example: CI/CD Pipeline

```bash
# Create setup configuration
cat > setup.json << 'EOF'
{
  "git_user_name": "CI Bot",
  "git_user_email": "ci@company.com",
  "namespace": "AutoDeployApp",
  "composer_name": "company/auto-deploy",
  "mysql_connection": "mysql://root:secret@db-server/production",
  "timezone": "UTC",
  "install_examples": false
}
EOF

# Run unattended setup
composer create-project byjg/rest-reference-architecture production-app ^6.0

# setup.json is automatically deleted after reading
```

## Interactive Mode

If `setup.json` does not exist, the setup runs in interactive mode (default behavior) and prompts for all configuration values.

## Security Note

The `setup.json` file is listed in `.gitignore` to prevent accidentally committing it to version control. Make sure to keep it outside of your project directories or add it to your global gitignore if it contains sensitive information.
