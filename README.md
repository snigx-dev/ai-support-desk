# AI Support Desk

A Laravel 13 AI-powered support system that automates ticket analysis, retrieval, and response generation using structured AI workflows, embeddings, and RAG.

---

## 🚀 Overview

AI Support Desk demonstrates how to build a production-grade AI backend using Laravel, combining:

- Event-driven architecture
- Queue-based AI processing
- Structured AI outputs (DTOs)
- Embeddings + semantic search (pgvector)
- Retrieval Augmented Generation (RAG)

The system behaves like an internal AI support agent similar to Intercom/Zendesk AI.

---

## 🧠 Core Features

- AI ticket classification (priority, category, summary)
- AI-generated support replies
- Context-aware responses using similar tickets (RAG)
- Semantic search via embeddings (pgvector)
- Async AI processing pipeline
- Structured AI outputs (no raw text parsing)
- Retry-safe queued workflows

---

## 🏗️ Architecture Highlights

### Event-Driven Pipeline

Ticket lifecycle is fully asynchronous:

```
Ticket Created
   ↓
Event Dispatch
   ↓
Queue Jobs
   ├── Ticket Analysis (AI)
   ├── Embedding Generation
   ├── Similar Ticket Search
   └── AI Reply Generation (RAG)
```

---

### AI Layer Design

- AI logic isolated in dedicated services
- Structured outputs enforced via DTOs
- Prompt logic separated from business logic
- No direct AI calls in controllers

---

### Retrieval Augmented Generation (RAG)

AI responses are enhanced using:

- Top similar historical tickets
- Embedding-based similarity search
- Context injection before generation

---

## 🧱 Tech Stack

- Laravel 13
- Laravel AI SDK
- Laravel Boost (Codex integration)
- PostgreSQL + pgvector
- Redis (queues)
- Livewire
- TailwindCSS
- Pest (testing)

---

## 📦 Key Engineering Concepts

- Event-driven architecture
- CQRS-inspired separation (actions/services)
- DTO-first AI responses
- Queue-first processing model
- Vector-based semantic search
- AI workflow isolation
- Deterministic prompt engineering
- Fail-safe AI execution (retries + fallbacks)

---

## ⚙️ Setup

```bash
git clone <repo-url>
cd ai-support-desk

composer install
npm install

cp .env.example .env
php artisan key:generate

php artisan migrate
php artisan queue:work
npm run dev
```

---

## 🔑 Environment Variables

```
OPENAI_API_KEY=your-key
DB_CONNECTION=pgsql
REDIS_CLIENT=redis
```

---

## 🧪 Testing

```bash
php artisan test
```

- Feature & unit tests via Pest
- AI workflows tested with fakes/mocks
- Queue-based testing included

---

## 📌 Summary

This project demonstrates how to build a real-world AI backend system using Laravel with:

- structured AI workflows
- embeddings + semantic search
- event-driven async architecture
- production-grade code organization

---
