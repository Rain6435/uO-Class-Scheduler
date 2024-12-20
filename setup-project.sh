#!/bin/bash

# Create app directory structure
mkdir -p app/{Console,DTOs,Exceptions,Http,Models,Providers,Repositories,Services}
mkdir -p app/Console/Commands
mkdir -p app/Http/{Controllers,Middleware,Requests,Resources}
mkdir -p app/Http/Controllers/API
mkdir -p app/Http/Controllers/Auth
mkdir -p app/Http/Requests/{Auth,Course,Schedule,Timetable}
mkdir -p app/Repositories/Contracts
mkdir -p app/Services/{DataSync,Parser,Scraper}
mkdir -p app/Services/Scraper/Contracts

# Create the core files in app directory
# Console Commands
touch app/Console/Commands/{SyncCourseCatalogCommand.php,SyncImportantDatesCommand.php,SyncProfessorRatingsCommand.php,SyncTimetablesCommand.php}

# DTOs
touch app/DTOs/{CourseData.php,ImportantDateData.php,ProfessorData.php,SectionData.php,SavedScheduleData.php}

# Exceptions
touch app/Exceptions/{DataSyncException.php,ScraperException.php}

# Controllers
touch app/Http/Controllers/Controller.php
touch app/Http/Controllers/ProfileController.php
touch app/Http/Controllers/API/{CourseController.php,ImportantDateController.php,ProfessorController.php,TimetableController.php,SavedScheduleController.php}
touch app/Http/Controllers/Auth/{AuthenticatedSessionController.php,ConfirmablePasswordController.php,EmailVerificationNotificationController.php,EmailVerificationPromptController.php,NewPasswordController.php,PasswordController.php,PasswordResetLinkController.php,RegisteredUserController.php,VerifyEmailController.php}

# Middleware
touch app/Http/Middleware/HandleInertiaRequests.php

# Requests
touch app/Http/Requests/Auth/LoginRequest.php
touch app/Http/Requests/Course/{IndexRequest.php,SearchRequest.php}
touch app/Http/Requests/Schedule/{StoreRequest.php,UpdateRequest.php}
touch app/Http/Requests/Timetable/{IndexRequest.php,SearchRequest.php}
touch app/Http/Requests/ProfileUpdateRequest.php

# Resources
touch app/Http/Resources/{CourseResource.php,ImportantDateResource.php,ProfessorResource.php,SectionResource.php,SavedScheduleResource.php}

# Models
touch app/Models/{Course.php,CourseSection.php,ImportantDate.php,Professor.php,SavedSchedule.php,SectionSchedule.php,Subject.php,Term.php,User.php}

# Providers
touch app/Providers/AppServiceProvider.php

# Repositories
touch app/Repositories/Contracts/{CourseRepositoryInterface.php,ProfessorRepositoryInterface.php,TimetableRepositoryInterface.php,ScheduleRepositoryInterface.php}
touch app/Repositories/{CourseRepository.php,ProfessorRepository.php,TimetableRepository.php,ScheduleRepository.php}

# Services
touch app/Services/DataSync/{CourseSyncService.php,ImportantDatesSyncService.php,ProfessorSyncService.php,TimetableSyncService.php}
touch app/Services/Parser/{CourseParser.php,ImportantDatesParser.php,RateMyProfessorParser.php,TimetableParser.php}
touch app/Services/Scraper/Contracts/{ParserInterface.php,ScraperInterface.php}
touch app/Services/Scraper/{CourseCatalogScraper.php,ImportantDatesScraper.php,RateMyProfessorScraper.php,TimetableScraper.php}

# Create config directory
mkdir -p config
touch config/{app.php,auth.php,cache.php,database.php,filesystems.php,logging.php,mail.php,queue.php,scraper.php,services.php,session.php}

# Create database directory
mkdir -p database/{factories,migrations,seeders}
touch database/.gitignore
touch database/uo-class-scheduler.sqlite

# Create factories
touch database/factories/{CourseFactory.php,ProfessorFactory.php,SectionFactory.php,UserFactory.php,SavedScheduleFactory.php}

# Create migrations
touch database/migrations/0001_01_01_000000_create_users_table.php
touch database/migrations/0001_01_01_000001_create_cache_table.php
touch database/migrations/0001_01_01_000002_create_jobs_table.php
touch database/migrations/2024_01_01_000001_create_subjects_table.php
touch database/migrations/2024_01_01_000002_create_courses_table.php
touch database/migrations/2024_01_01_000003_create_course_prerequisites_table.php
touch database/migrations/2024_01_01_000004_create_terms_table.php
touch database/migrations/2024_01_01_000005_create_course_sections_table.php
touch database/migrations/2024_01_01_000006_create_section_schedules_table.php
touch database/migrations/2024_01_01_000007_create_professors_table.php
touch database/migrations/2024_01_01_000008_create_professor_section_table.php
touch database/migrations/2024_01_01_000009_create_important_dates_table.php
touch database/migrations/2024_01_01_000010_create_saved_schedules_table.php
touch database/migrations/2024_01_01_000011_create_saved_schedule_sections_table.php

# Create seeders
touch database/seeders/{DatabaseSeeder.php,SubjectSeeder.php,TermSeeder.php}

# Create tests directory
mkdir -p tests/{Feature,Unit,Datasets}
mkdir -p tests/Feature/{Courses,ImportantDates,Professors,Schedules,Timetables}
mkdir -p tests/Unit/{Models,Repositories,Services}

# Create test files
touch tests/Pest.php
touch tests/Datasets/{CourseData.php,ProfessorData.php,TimetableData.php}

# Feature tests
touch tests/Feature/Courses/{ListCoursesTest.php,SearchCoursesTest.php,ShowCourseDetailsTest.php}
touch tests/Feature/ImportantDates/{FilterImportantDatesTest.php,ListImportantDatesTest.php}
touch tests/Feature/Professors/{ListProfessorsTest.php,ShowProfessorRatingsTest.php}
touch tests/Feature/Schedules/{CreateScheduleTest.php,DeleteScheduleTest.php,ListSchedulesTest.php,UpdateScheduleTest.php}
touch tests/Feature/Timetables/{FilterTimetablesTest.php,ListTimetablesTest.php,SearchTimetablesTest.php}

# Unit tests
touch tests/Unit/Models/{CourseTest.php,ProfessorTest.php,SectionTest.php,TermTest.php,SavedScheduleTest.php}
touch tests/Unit/Repositories/{CourseRepositoryTest.php,ProfessorRepositoryTest.php,TimetableRepositoryTest.php,ScheduleRepositoryTest.php}
touch tests/Unit/Services/{CourseCatalogScraperTest.php,ImportantDatesScraperTest.php,RateMyProfessorScraperTest.php,TimetableScraperTest.php}

# Set permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod 755 artisan

# Create setup script
touch setup-project.sh
chmod 755 setup-project.sh