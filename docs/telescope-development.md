# Laravel Telescope Development Tool

## Overview

Poet-Web uses Laravel Telescope as a local development and debugging tool.

Telescope is not part of the application's user-facing functionality. It is used during development to inspect what Laravel is doing internally.

Telescope can show:

- HTTP requests;
- database queries;
- exceptions;
- logs;
- model events;
- cache operations;
- queued jobs;
- commands;
- events.

---

## Installation

Telescope is installed as a development dependency:

```bash
composer require laravel/telescope --dev