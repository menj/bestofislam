#!/bin/sh
# Points git at .githooks so the image check runs before every commit.
git config core.hooksPath .githooks
chmod +x .githooks/pre-commit
echo "Hooks enabled."
