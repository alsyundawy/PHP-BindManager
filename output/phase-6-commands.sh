#!/usr/bin/env bash
set -euo pipefail
composer phpcs
composer phpstan
composer psalm
composer test
