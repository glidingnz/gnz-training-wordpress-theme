---
applyTo: "**"
---

# Background

This is a Wordpress theme used to host Gliding New Zealand's Pilot Training Program. Usage is split between people using the heirarchical navigation to follow syllabuses, and people using the search function to find specific content. The site should work well for an elderly instructor looking for a specific lesson on the side of an airfield on a mobile device.

# Testing

The site runs at `http://localhost:8080`. You can use the browser's developer tools to inspect the site and test changes. For testing PHP code, you can use the command line as described below.

## Playwright

Playwright is configured with the `html` reporter, which automatically starts a local web server to display results after a test run. This causes the process to hang indefinitely in an agent context. Always pass `--reporter=line` when running Playwright tests so the process exits cleanly:

```bash
npx playwright test --reporter=line
```

Never run with `--update-snapshots` unless the user has explicitly asked you to update the visual baselines. Running it without permission silently overwrites the reference images, defeating the purpose of regression testing.

# Project general coding standards

- Don't make any big assumptions about design/functionality. The user prefers clarifying questions.
- Challenge the user when appropriate to ensure high-quality outcomes.
- Suggest alternative approaches if they might better meet the user's needs.
- Minimise JavaScript to keep performance high and complexity low.

# Running PHP commands

We're using Docker Compose, so use the following command structure to run PHP commands:

```bash
docker compose -f ../../../../docker-compose.yml exec wordpress sh -c "php -i"
```
