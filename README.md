# Syaikhuna

## Digital Operating System for Islamic Knowledge & Da'wah in Borneo

**Syaikhuna** is a web and mobile platform dedicated to preserving, managing, and distributing the Islamic scholarly heritage of *Ahlussunnah wal Jama'ah* across South, Central, and East Kalimantan (Borneo, Indonesia).

The platform is designed to connect congregants, students, academics, and professionals with a network of Islamic study circles (*majelis ilmu*) led by the descendants of **Shaykh Muhammad Arsyad Al-Banjari (Datu Kalampayan)** and the students of **KH. Muhammad Zaini bin Abdul Ghani (Abah Guru Sekumpul)**.

Rather than serving merely as a static religious event directory, Syaikhuna aims to become an intelligent ecosystem that integrates collaborative knowledge documentation, community management, AI-powered digital libraries, and large-scale religious event logistics.

---

# 🚀 Key Features

## 1. Live Religious Map & Dynamic Schedules

A real-time monitoring system for religious gatherings and study circles.

### Features

* Live schedule status:

  * 🟢 Open
  * 🔴 Closed / Holiday
  * 🟡 Tentative
* Authorized schedule management by official representatives of each study circle.
* Interactive map integration for navigation and travel routes.

---

## 2. Digital Sinoman (Community Funeral Mutual Aid System)

A digital transformation of the traditional Banjar community mutual-aid system for funeral services (*Fardhu Kifayah*) and bereavement support.

### Features

* Automatic membership contribution payments via:

  * QRIS
  * Virtual Accounts
* Improved financial transparency and reduced unpaid contributions.
* Emergency **"Report Bereavement"** panic button for rapid volunteer mobilization.

---

## 3. Smart Library & AI Chatbot

### Core Capabilities

#### Chat with Books

Users can interact directly with verified Islamic manuscripts and reference works using Retrieval-Augmented Generation (RAG).

Examples include:

* *Sabilal Muhtadin*
* Classical Islamic texts
* Verified religious publications

#### Semantic Search

Search by meaning rather than exact keywords.

The system can identify relevant passages across thousands of pages of:

* Religious books
* Study notes
* Academic publications
* Historical documents

### Technologies

* Retrieval-Augmented Generation (RAG)
* Vector Embeddings
* Semantic Search
* PostgreSQL + pgvector

---

## 4. Podcast Generator (Audio-Based Learning)

Automatically transforms written content into engaging audio discussions.

### Supported Content

* Religious manuscripts
* Scholar biographies (*Manaqib*)
* Academic articles
* Study notes

### Benefits

* Supports learning during commuting.
* Suitable for professionals and university students.
* Encourages continuous engagement with Islamic knowledge.

---

## 5. Collaborative Study Notes (Risalah Jamaah)

A collaborative note-taking platform for attendees of religious gatherings.

### Workflow

1. Participants create study notes.
2. Notes are linked to specific events or study sessions.
3. Official administrators perform **Tashih** (verification and correction).
4. Approved notes become searchable and publicly accessible.

### Objectives

* Prevent quotation errors.
* Preserve scholarly accuracy.
* Build a long-term community knowledge archive.

---

# 🛠️ Technology Stack

Syaikhuna is designed to run efficiently on affordable VPS infrastructure with limited resources (< 4 GB RAM).

| Layer              | Technology                                   |
| ------------------ | -------------------------------------------- |
| Frontend           | Tailwind CSS, Alpine.js                      |
| Backend            | Laravel, Livewire                            |
| Framework          | Laravel TALL Stack                           |
| Database           | PostgreSQL                                   |
| Vector Search      | pgvector                                     |
| AI Services        | Open Notebook (FastAPI + Python + SurrealDB) |
| Containerization   | Docker                                       |
| Push Notifications | OneSignal                                    |
| Messaging          | WhatsApp Business API                        |

### Architecture Benefits

* SEO-friendly
* Lightweight resource consumption
* Server-side rendering support
* AI-ready infrastructure
* Cost-efficient deployment

---

# 📦 VPS Installation Guide

## System Requirements

### Operating System

* Ubuntu 22.04 LTS
* Debian 11+

### Minimum Resources

* RAM: 2 GB
* Swap: 4 GB
* CPU: 2 vCPU

### Required Software

* Docker
* Docker Compose
* PHP 8.2+
* Composer
* PostgreSQL
* pgvector Extension

---

## Open Notebook AI Service Setup

### Create Working Directory

```bash
mkdir -p ~/open-notebook
cd ~/open-notebook
```

### Create Docker Compose Configuration

```bash
nano docker-compose.yml
```

Add the required deployment configuration.

### Start Services

```bash
docker compose up -d
```

---

# 🗄️ Core Database Structure

## `seasonal_schedules`

Stores recurring schedules and religious events.

### Examples

* Fajr Lectures
* Ramadan Programs
* Mawlid Gatherings
* Annual Commemorations

### Benefits

* Reduces data duplication.
* Optimizes storage.
* Improves query performance.

---

## `scientific_articles`

Stores scholarly and public-facing scientific publications contributed by partner institutions.

| Column        | Type        | Description                          |
| ------------- | ----------- | ------------------------------------ |
| id            | BigInt (PK) | Unique article identifier            |
| foundation_id | Int (FK)    | Associated institution or foundation |
| title         | Varchar     | Article title                        |
| slug          | Varchar     | SEO-friendly URL                     |
| notebook_id   | Varchar     | Linked Open Notebook identifier      |
| views_count   | BigInt      | Total page views                     |
| likes_count   | BigInt      | Community appreciation count         |

---

# 🧠 One-Time Indexing Architecture

To support semantic search across thousands of study notes while minimizing API costs, Syaikhuna implements a **One-Time Indexing RAG pipeline**.

## 1. Queue System

After a note passes the verification process:

```text
Study Note → Laravel Queue → Embedding Process
```

---

## 2. Text Chunking

Documents are automatically divided into:

* Maximum 500 tokens per chunk
* 10% overlap

This preserves contextual continuity between chunks.

---

## 3. Batch Embedding

Every midnight:

1. Laravel collects newly approved content.
2. Data is sent in batches through the OpenAI Batch API.
3. Embeddings are generated using:

```text
text-embedding-3-small
```

### Cost Efficiency

| Method       | Cost per 1M Tokens |
| ------------ | ------------------ |
| Standard API | ~$0.02             |
| Batch API    | ~$0.01             |

Up to **50% cost reduction** compared to standard embedding requests.

---

## 4. Vector Storage

Embedding vectors are stored in PostgreSQL using pgvector.

### Specifications

* 1,536 dimensions
* Native vector data type
* Local similarity computation

---

## 5. Local Semantic Retrieval

User searches are performed entirely within the local database:

```sql
embedding <=> query_embedding
```

### Advantages

* No repeated external API calls.
* Fast response times.
* Predictable operational costs.
* Full control over data ownership.

---

# 📜 License & Ethical Usage

Syaikhuna is developed with a strong commitment to preserving scholarly integrity and respecting intellectual property rights.

## Principles

### 1. Content Licensing

All digital manuscripts and publications must have:

* Written permission from copyright holders; or
* A valid publishing or digital licensing agreement.

### 2. Responsible AI Usage

Artificial Intelligence is used exclusively as:

* A knowledge retrieval assistant
* A semantic search engine
* A source discovery tool

AI must **not** be treated as an independent authority for issuing religious rulings (*fatwas*).

### 3. Prohibited Uses

The platform must not be used for:

* Practical political campaigns
* Sectarian propaganda
* Distribution of teachings outside the traditional *Ahlussunnah wal Jama'ah* framework represented by the platform

---

# 🤝 Vision

> Preserving scholarly chains of transmission, supporting communities on their journey toward knowledge, and harnessing technology to serve faith, learning, and future generations.

---

## Dedication

**Syaikhuna** is a contribution from the youth of Borneo dedicated to safeguarding, documenting, and disseminating Islamic scholarly heritage for generations to come.
