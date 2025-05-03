# Development Log

This log documents the progress of the Openverse Media Search project from March 1 to May 3, 2025.

## March 1, 2025
- Initialized Git repository
- Created basic folder structure (frontend, backend, db, docker)
- Defined initial project requirements and module breakdown

## March 3–5, 2025
- Developed Authentication module
- Implemented secure registration and login (PHP sessions and password hashing)
- Added validation for form inputs and user feedback messages

## March 6–10, 2025
- Developed Search module
- Integrated Openverse API using AJAX
- Handled failed API requests with error messages
- Added loading indicators for real-time search feedback

## March 11–15, 2025
- Created User Dashboard
- Built functions for saving media, viewing saved items, and search history
- Updated MySQL database schema to support user content

## March 16–18, 2025
- Developed Admin Dashboard
- Added CRUD operations for users and saved content
- Implemented access control and admin login

## March 19–22, 2025
- Refactored backend into OOP structure using PHP classes
- Improved file organization and modularity
- Updated folder structure to separate frontend and backend logic

## March 23–27, 2025
- Wrote unit tests using PHPUnit
- Tested search and authentication functions
- Fixed bugs in user dashboard and admin module

## March 28–30, 2025
- Implemented Docker containerization
- Created Dockerfile and `docker-compose.yml` to orchestrate backend and database
- Configured `.env` for environment variables

## April 1, 2025
- Final round of integration testing
- Performed UI adjustments for responsiveness
- Uploaded documentation and README to GitHub

## April 5–10, 2025
- Reviewed project for technical report
- Added project architecture diagrams (UML class, use case, sequence diagrams)
- Began writing project documentation (`feature_development.md` and `development_log.md`)

## April 12–18, 2025
- Added GitHub Issues for tracking optional improvements
- Cleaned up codebase (removed unused files, standardized comments)
- Improved database normalization for search history

## April 20–25, 2025
- Created screenshots for documentation
- Verified Docker setup on clean test environments (Windows and Linux)
- Peer-reviewed documentation for consistency and grammar

## April 26–30, 2025
- Final walkthrough of all project features
- Deployed to live Docker host for testing
- Wrote deployment notes and included them in documentation

## May 1–3, 2025
- Completed final testing on production environment
- Final deployment of the system with Docker Compose on a VPS
- Final documentation push to GitHub
- Backup created and archived
- Project ready for submission

## Notes
The GitHub commit history and branch logs reflect the above steps. Each feature was implemented in isolation using Git branches and merged after proper testing. Documentation was progressively maintained throughout development.
