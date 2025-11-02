---
sidebar_position: 14
---

# Running on Windows Without PHP

This project is primarily designed for Linux environments, but can be easily run on Windows using Docker.

## Prerequisites

- Docker Desktop installed and running on your Windows machine
- No need for a local PHP installation or WSL2 configuration

## Quick Start

1. Open Command Prompt or PowerShell
2. Navigate to your desired project location:

```textmate
cd C:\Users\MyUser\Projects
```

3. Launch a containerized PHP environment with the following command:

```textmate
docker run -it --rm -v %cd%:/root/tutorial -w /root/tutorial byjg/php:8.4-cli bash
```

4. Once inside the container shell, you can run all PHP commands normally as if you had PHP installed locally. 
The docker commands you'll run outside.

**Note:**
> Inside the container shell, the folder `~/tutorial` or else `/root/tutorial`
is mapped to your current directory on Windows.

Once inside the container, follow the regular [Getting Started](getting_started.md) guide.

---

**[← Previous: Add a New Rest Method](getting_started_03_create_rest_method.md)** | **[Back to Index](../README.md)**
