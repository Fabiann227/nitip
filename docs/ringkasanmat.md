# Ringkasan Materi Perkuliahan: Systems Analysis & Design (SYS24031)
- **Program Studi:** Sistem Informasi — Universitas Pelita Harapan (UPH)
- **Dosen Pengampu:** Calandra Alencia Haryani, S.E., S.Si., M.T.I.
- **Referensi Utama:** Dennis, Wixom, & Roth, *Systems Analysis and Design*, 8th ed. (John Wiley & Sons, 2021) + Praktik Terintegrasi Kelas (BPMN, Implementasi Web, Traceability, & Functional Black-Box Testing).

---

## Filosofi & Prinsip Utama Mata Kuliah
1. **Core Principle:** *"Before we build the system, we must understand the problem, process, requirements, and logic behind it."*
2. **Progressive Precision:** *"Every stage should make the next stage easier and more precise."*
3. **One Project, One Story:** `Problem → Analysis → Design → Implementation → Validation`.
4. **AI & Tooling Literacy:** Tools dan AI mempercepat implementasi, tetapi analisis yang disiplin menentukan apakah kita membangun sistem yang tepat (*"Tools and AI can accelerate implementation; disciplined analysis determines whether we are building the right system"*). AI bertindak sebagai *Copilot*, bukan *Autopilot*.

---

## Peta Jalan Perkuliahan per Fase & Session (P1–P16)

### Stage 1: UNDERSTAND (Pertemuan 1–4)
Fokus: Fondasi analisis, inisiasi proyek, analisis proses bisnis (BPMN), dan rekayasa kebutuhan (*requirements*).

- **Session 1 — Introduction to Systems Analysis & Design:**
  - Perbedaan *Problem Understanding, Analysis, Design,* dan *Coding*.
  - Spesifikasi presisi sebelum membangun aplikasi:
    1. *Who are the users / actors?*
    2. *What business problem are we solving?*
    3. *What can each user do (functional requirements)?*
    4. *What business rules apply?*
    5. *What happens when exceptions/errors occur?*
  - Format spesifikasi terstruktur: `Build [system scope] for [actors]. The system shall [requirements]. Apply these rules: [business rules]. Handle these exceptions: [exceptions].`

- **Session 2 — Project Initiation, Feasibility, & Standards (Ch. 1–2 Dennis):**
  - **System Request:** Project Sponsor, Business Need, Business Requirements, Business Value (*tangible* & *intangible*), Special Issues/Constraints.
  - **Feasibility Analysis:** Technical Feasibility, Economic Feasibility, Organizational Feasibility.
  - **Project Standards & Documentation:** Documentation standards, Coding standards, Procedural standards, Specification requirement standards, User Interface design standards, serta pengelolaan *Project Binder* / dokumentasi berkelanjutan.

- **Session 3 — Business Process Analysis with BPMN:**
  - Prinsip: *"Do not automate a bad process faster."*
  - Alur analisis proses: `AS-IS → PROCESS DIAGNOSIS (PAIN POINTS) → IMPROVE → TO-BE`.
  - **Aturan Pemisahan Model:** BPMN memodelkan proses bisnis *end-to-end* lintas partisipan organisasi (*Pools & Lanes*, *Start/End Event*, *Task*, *Gateway*, *Sequence Flow*, *Handoff*). **Jangan tertukar dengan UML Activity Diagram.**
  - **3 Artefak Wajib Analisis Proses:**
    1. **BPMN As-Is:** Kondisi proses berjalan saat ini.
    2. **Process Diagnosis:** Membedakan *Symptom* (gejala permukaan) vs *Root Cause* (akar penyebab), mengidentifikasi *handoff* manual yang rawan keterlambatan/duplikasi, *bottleneck*, dan aktivitas *non-value-added*.
    3. **BPMN To-Be:** Proses baru setelah perbaikan (minimal 3 perbaikan konkret, nilai perbaikan, dan peran spesifik teknologi/sistem web).

- **Session 4 — Requirements Engineering & Elicitation (Ch. 3 Dennis):**
  - **Formal vs. Informal System (Observation + Document Analysis):** *"People often describe the official process, not the real process."* Membandingkan *What the SOP says* vs *What actually happens* (workaround manual, chat reminder, inkonsistensi data) untuk menemukan *Hidden Requirements*.
  - **Requirements Definition:**
    - *Business Requirements (BR)*
    - *Functional Requirements (FR)* — layanan/fungsi yang harus dilakukan sistem.
    - *Non-Functional Requirements (NFR)* — 4 kategori: *Operational, Performance, Security,* dan *Cultural & Political*.
  - Kompilasi **System Proposal** (berisi *Executive Summary*, *System Request*, *Feasibility*, *Requirements*, *Process Model*, *Data Model*, dan *Appendices*).

---

### Stage 2: FUNCTIONAL MODELING (Pertemuan 5–7)
Fokus: Memodelkan perilaku fungsional sistem dari sudut pandang pengguna dan alur aktivitas (Ch. 4 Dennis).
- **Use Case Diagram:** *System Boundary*, *Actors*, *Use Cases*, serta relasi `<<include>>` dan `<<extend>>`.
- **Use Case Descriptions (Fully Dressed):** Use Case ID & Name, Primary Actor, Stakeholders, Trigger, Preconditions, Normal Flow, Alternative/Exception Flows, dan Postconditions.
- **Activity Diagram:** Memodelkan alur aktivitas detail pada level sistem / per *Use Case* menggunakan *swimlanes*, *decision/merge*, dan *fork/join*.

---

### Stage 3: MIDTERM CHECKPOINT (Pertemuan 8)
- Evaluasi konsistensi analisis berbasis studi kasus (*Problem → BPMN → Requirements → Functional Models*).

---

### Stage 4: STRUCTURAL MODELING (Pertemuan 9–10)
Fokus: Memodelkan struktur objek, atribut, perilaku kelas, dan relasi data sistem (Ch. 5 Dennis).
- **Object Identification:** *Textual analysis* (*Nouns → Classes*, *Verbs → Operations*), *Brainstorming*, *Common Object Lists*, dan *Patterns*.
- **CRC Cards (Class-Responsibility-Collaboration):**
  - **Front Side:** *Class Name*, *ID*, *Type (Concrete, Domain)*, *Description*, *Associated Use Cases*, *Responsibilities* (tugas/operasi), dan *Collaborators* (kelas mitra dalam pola *Client–Server–Contract*).
  - **Back Side:** *Attributes* dan *Relationships* (*Generalization / a-kind-of*, *Aggregation / has-parts*, *Other Associations*).
- **UML Class Diagram & Object Diagram:**
  - Kelas, visibility (`-`, `+`, `#`), atribut, operasi/method.
  - Jenis relasi: *Association*, *Aggregation*, *Composition*, *Generalization (Inheritance)*.
  - **Multiplicity:** `1` (Exactly one: `1..1`), `0..*` (Zero or more), `1..*` (One or more).
  - **Object Diagram:** Contoh instansiasi objek nyata dengan nilai atribut spesifik.

---

### Stage 5: INTEGRATE — Behavior, System Design, Construction & Testing (Pertemuan 11–14)
Fokus: Menghubungkan model fungsional dan struktural ke desain teknis, konstruksi web, ketertelusuran, dan pengujian (Ch. 6–12 Dennis).
- **Behavioral Modeling (Ch. 6 Dennis):** *Sequence Diagram* / *Communication Diagram* / *Behavioral State Machine* yang menunjukkan interaksi dinamis antarobjek berdasarkan skenario *Use Case*.
- **System & Program Design (Ch. 7–10 Dennis):**
  - Verifikasi kelas agar *sufficient and necessary* (evaluasi *high cohesion* dan *loose coupling*).
  - Pemisahan *Problem Domain Layer* dengan *Data Management Layer* (penyimpanan permanen: *Master files*, *Look-up files*, *Transaction files*).
  - *Architecture Design*, *User Interface Design Templates / Navigation*, *Physical Data Model / Normalization (3NF)*, dan *Structure Chart* (*sequence*, *selection*, *iteration*, *library modules*).
- **Configuration / Construction Phase & Documentation (Ch. 11–12 Dennis):**
  - Manajemen lingkungan (*Development*, *Testing*, *Production*) dan *Change Control*.
  - **3 Jenis User Documentation:**
    1. *Reference Documents* (panduan melakukan fungsi/perintah spesifik).
    2. *Procedure Manuals* (panduan menjalankan tugas bisnis yang mencakup beberapa langkah/tugas).
    3. *Tutorials* (panduan belajar menggunakan komponen utama sistem secara berurutan).
- **End-to-End Traceability & Functional Black-Box Testing:**
  - **Traceability Matrix:** Keterlacakan dua arah antara `Business Process / Requirements ↔ Use Case ↔ Class / DB ↔ Source Code ↔ Test Case`.
  - **Designing Test (Functional Black-Box Testing):** Pengujian skenario positif (*Normal Flow*) dan negatif (*Alternative/Exception Flow*) untuk memvalidasi kebenaran perilaku aplikasi web tanpa bergantung pada struktur internal semata.

---

### Stage 6: DEFEND (Pertemuan 15–16)
Fokus: Finalisasi dokumentasi proyek terintegrasi, demonstrasi aplikasi web (*live demo*), dan sesi tanya jawab (*Q&A Defense*).