---
applyTo: "**"
---
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
