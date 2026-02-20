# MAHIR: Headless AI-Powered Multi-Tenant Platform

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0)
[![GitHub Stars](https://img.shields.io/github/stars/syahrinseth/mahir?style=social)](https://github.com/syahrinseth/mahir)

---

## What is MAHIR?

MAHIR is a **headless, multi-tenant, modular platform** that combines:

- **AI Agent Framework** — Multi-provider intelligent automation (OpenAI, Anthropic, Gemini)
- **Modular Architecture** — Blog, Portfolio, Clinic modules + extensible design
- **Enterprise-Grade Security** — Ready encryption, immutable audit logs, role-based access
- **Flexible Deployment** — Self-hosted Docker/Kubernetes or managed cloud SaaS
- **Open-Source & Free** — AGPL v3 licensed, no vendor lock-in, community-driven

Built for **digital agencies, healthcare providers, creative studios, and enterprises** who need intelligent automation without sacrificing data sovereignty.

---

## Why "Headless"?

MAHIR is **API-first, not UI-first**:

- No opinionated frontend (use React, Vue, SwiftUI, or your own)
- Flexible integrations (REST API, WebSocket, CLI)
- Fits your tech stack (not forced into one)
- Modular (use only what you need)

---

## Key Features

### AI-Powered Agent Automation

- **Multi-Provider Support** — OpenAI (GPT-4), Anthropic (Claude), Google Gemini, with automatic failover
- **Structured Output** — Agents return JSON, not just text
- **Async Queueing** — Fire-and-forget long operations, poll for results
- **Cost Tracking** — See exactly what each AI action costs
- **No Rate Limit Hell** — Built-in, configurable per-scope limits

### Modular Design

- **Blog Module** — Content, SEO, AI-powered generation
- **Portfolio Module** — Projects, tech stacks, AI-powered descriptions
- **Clinic Module** — HIPAA-ready patient management with encryption
- **Extensible** — Build your own modules on the same foundation

### Multi-Tenant Architecture

- **Data Isolation** — Complete isolation per organization
- **Scope-Based Access** — Fine-grained permissions (blog:create, clinic:read, etc.)
- **Tenant-Aware Queries** — Automatic query scoping, no data leaks
- **Shared Infrastructure** — Cost-efficient deployment model

### Enterprise Security

- **HIPAA-Compliant** — Patient PII encrypted at rest (AES-256)
- **Immutable Audit Logs** — Every action tracked, tamper-proof
- **Role-Based Access Control** — JWT + Bearer token authentication
- **Rate Limiting** — Per-agent, per-scope, configurable
- **Compliance-Ready** — For healthcare, finance, and regulated industries

### Flexible Deployment

- **Docker Compose** — Self-hosted in 5 minutes
- **Kubernetes** — Enterprise-grade scaling
- **Managed Cloud** — mahir.syahrinseth.com SaaS (zero-ops)
- **Hybrid** — Use both, migrate freely
- **Offline Licensing** — Air-gapped deployments supported

---

## Quick Start

### Self-Hosted (5 minutes)

**Prerequisites:** PHP 8.2+, Composer, Node.js 18+, npm

```bash
# Clone repository
git clone https://github.com/syahrinseth/mahir.git
cd mahir

# Install dependencies, generate app key, run migrations, and build assets
composer setup

# Start the development server
composer run dev
```

The app will be available at [http://mahir.test](http://mahir.test) if using [Laravel Herd](https://herd.laravel.com), or at `http://localhost:8000` via `php artisan serve`.

### Cloud SaaS (< 1 minute)

Visit [mahir.syahrinseth.com](https://mahir.syahrinseth.com) → Sign up → Start building

---

## Architecture at a Glance

```
┌─────────────────────────────────────────────┐
│  MAHIR: Headless Multi-Tenant Platform      │
├─────────────────────────────────────────────┤
│                                             │
│  Frontend (Your Choice)                     │
│  ├─ React Web App                          │
│  ├─ React Native Mobile                    │
│  ├─ Custom UI                              │
│  └─ Third-party dashboard                  │
│           ↓ (REST API / WebSocket)          │
│  ┌─────────────────────────────────────────┤
│  │  MAHIR API (Laravel)                    │
│  │  ├─ Authentication & Scopes             │
│  │  ├─ Multi-Tenant Scoping                │
│  │  ├─ AI Agent Framework                  │
│  │  └─ Audit Logging                       │
│  └─────────────────────────────────────────┤
│           ↓                                  │
│  Modular Components                         │
│  ├─ Blog (CRUD + AI Generation)            │
│  ├─ Portfolio (CRUD + Optimization)        │
│  ├─ Clinic (CRUD + Encryption)             │
│  └─ Custom Modules                         │
│           ↓                                  │
│  Infrastructure                             │
│  ├─ MySQL (Multi-Tenant DB)                │
│  ├─ Redis (Cache + Rate Limiting)          │
│  └─ Queue (Async Jobs)                     │
│                                             │
└─────────────────────────────────────────────┘
```

---

## Use Cases

### Content Agencies

Generate 50+ blog posts/week with consistent brand voice. MAHIR agents write, optimize SEO, and format automatically.

### Healthcare Providers

Centralized patient management (appointments, consent forms, encrypted records) with HIPAA audit trails.

### Design & Portfolio Studios

Auto-generate portfolio descriptions, SEO tags, and marketing copy from project metadata.

### E-Commerce (Coming)

Product descriptions, inventory management, customer insights — all AI-powered.

---

## Technology Stack

| Component | Technology | Why |
|---|---|---|
| Language | PHP 8.3 | Modern type system, performance |
| Framework | Laravel 12 | Modern, elegant, AI-native |
| Database | SQLite | Zero-config, lightweight, portable |
| Queue | Database | Async jobs, cost tracking |
| Cache | Database | Simple, no extra dependencies |
| Frontend | Vite + Tailwind CSS 4 | Fast builds, utility-first styling |
| Testing | Pest 4 | Modern assertions, fast feedback |
| Code Style | Laravel Pint | Consistent, opinionated formatting |
| Local Server | Laravel Herd | Zero-config PHP dev environment |

---

## Modules

### Blog Module

- Post CRUD (create, read, update, delete)
- Categories, tags, SEO metadata
- AI-powered content generation
- Publish scheduling
- Analytics tracking
- Status: Stable & Open-Source

### Portfolio Module

- Project showcase with rich metadata
- Technology stacks & skills
- AI-powered description optimization
- Portfolio analytics
- Status: Stable & Open-Source

### Clinic Module

- Patient management (encrypted PII)
- Appointment scheduling
- Consent form workflows
- HIPAA-compliant audit logs
- Status: Stable (Basic) | Premium (Advanced)

---

## Getting Started

### For Developers

Soon

### For Businesses

Soon

### For Contributors

Soon

---

## Philosophy: Open-Source by Design

**Transparency Over Black Boxes**
Audit every line of code. Find vulnerabilities yourself. Contribute fixes that benefit everyone.

**Community Over Silos**
Enterprise software is better together. Our community improves MAHIR every day.

**Data Sovereignty Over Hostage Ransom**
Self-hosted MAHIR is yours forever. Export anytime. Migrate to cloud or back — your choice.

**Ethics Over Exploitation**
AGPL ensures fair competition. If someone uses MAHIR commercially, they share improvements back.

---

## Community

- **GitHub Issues** — Report bugs, request features
- **GitHub Discussions** — Ask questions, share ideas
- **Discord** — Real-time chat, support, networking
- **Twitter** — Updates, tips, announcements
- **Blog** — Tutorials, case studies, deep dives

---

## License

MAHIR is **AGPL v3 licensed** — free and open-source.

**What you can do:**

- Use for personal/private projects
- Self-host freely
- Modify and extend
- Commercial use (with exceptions)

**What's required if you offer MAHIR as a service:**

- Share your modifications back to the community

[Read Full License →](./LICENSE)

---

## Support

### Community (Free)

- GitHub Issues & Discussions
- Discord community channel
- Public documentation

### Priority Support (Paid)

- Email support (Starter+)
- Response time SLA
- Custom development available

---

## Roadmap

- **Q2 2025:** v0.1 — System core services
- **Q3 2025:** v0.2 — System modules (Blog, Portfolio, Clinic)
- **Q4 2025:** v0.3 — Enterprise features, AI model fine-tuning, marketplace

---

## FAQ

**Q: Is MAHIR truly open-source?**
A: Yes, AGPL v3 licensed. Full source code on GitHub.

**Q: Can I self-host?**
A: Yes, Docker Compose or Kubernetes. Own your infrastructure.

**Q: What about data privacy?**
A: Self-hosted data stays on your servers. Cloud SaaS is encrypted and tenant-isolated.

**Q: Can I modify MAHIR?**
A: Yes, full source code available. Share improvements under AGPL.

**Q: Do you offer support?**
A: Community (free) + paid support plans.

---

## Contributors

We love contributions!

- Found a bug? Open an issue
- Built a feature? Submit a PR
- Improved docs? We appreciate it
- Have ideas? Join discussions

---

## Acknowledgments

Built with love using:

- **Laravel** — The PHP framework for artisans
- **Laravel AI SDK** — Unified AI integration
- **Open-source community** — For inspiration and support

---

## Get Started Now

```bash
# Self-Host
git clone https://github.com/syahrinseth/mahir.git
cd mahir
docker-compose up

# Or try Cloud (free, no credit card)
# Visit https://mahir.syahrinseth.com
```

---

Let's build the future of AI-powered enterprise software together.

If MAHIR helps you, please star this repository!
