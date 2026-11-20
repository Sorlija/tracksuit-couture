# AI usage log

## Milestone 1: Setup, static frontend, ERD

### Tools used
- **Claude (Opus 5.5, claude.ai)**: generated the initial folder structure, `.gitignore`, `config.example.php`, the SPA router (`router.js`), the static Bootstrap views, CSS theme, draft ERD (Mermaid) and the first drafts of README.md and AGENTS.md.
- All generated code was reviewed, run locally and adjusted by me.

### Most important prompts
1. "Here are the project requirements (PDF). The project is Tracksuit Couture, an online tracksuit shop. Do Milestone 1: folder structure, static SPA frontend with all pages, draft ERD with at least 5 entities, README draft."

### Cases where the AI was wrong or produced poor code
1. **What was wrong:** AI kept adding unnecessary comments for certain files. 
   **How I noticed:** By reading through all the code and reviewing everything thorougly. 
   **How I fixed it:** Deleted the comments, noted to AI not to repeat the same mistake. 

## Milestone 2: Database and DAO layer

### Tools used
- **Claude (Opus 5.5, claude.ai)**: generated the MySQL schema with sample data (`database/tracksuit_couture.sql`), `Database.php`, `BaseDao.php` and the seven entity DAOs, and updated the ERD, README and AGENTS.md.
- I imported the `.sql` file in DBeaver and tested the DAO methods locally.

### Most important prompts
1. "Do Milestone 2: MySQL schema with at least 5 entities, primary and foreign keys, sample data in a .sql file, and a DAO layer with a BaseDao and CRUD for every entity using prepared statements. Credentials come from config.php."
2. _TODO: add any follow-up prompts you use._

### Cases where the AI was wrong or produced poor code
<!-- Required: at least 2 real cases from your own review. -->

1. **What was wrong:** / 
   **How I noticed:** /
   **How I fixed it:** /

