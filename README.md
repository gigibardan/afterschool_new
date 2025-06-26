# TechMinds Academy Afterschool - Sistem Preînregistrări

## Setup cPanel
1. Urcă config.php în `/includes/` (NU în git!)
2. Verifică permisiuni folder `/logs/` (755)
3. Testează conexiunea la baza de date

## Structura
- `/public/` - Frontend public
- `/admin/` - Dashboard admin  
- `/api/` - Backend endpoints
- `/includes/` - Config & funcții comune

## URL-uri
- Formular: https://afterschool.techminds-academy.ro/preinscrieri/
- Admin: https://afterschool.techminds-academy.ro/admin/

## Test local
```bash
php -S localhost:8000 -t public/