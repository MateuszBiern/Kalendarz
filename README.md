# 📅 Kalendarz zadań / Task Calendar

Projekt interaktywnego kalendarza do zarządzania zadaniami. Może służyć jako planer rodzinny, organizer osobisty lub narzędzie zespołowe. Aplikacja pozwala na dodawanie, edycję i usuwanie zadań przypisanych do konkretnych dni.

## 🔧 Technologie

- **Frontend**: HTML, CSS, JavaScript, FullCalendar.js
- **Backend**: PHP (programowanie proceduralne)
- **Baza danych**: MySQL
- **Autoryzacja**: Prosty system logowania użytkownika z sesją

## ✨ Funkcje

- Wyświetlanie zadań w formie kalendarza
- Dodawanie zadań przez kliknięcie w dzień
- Edytowanie i usuwanie zadań
- Zadania z opisem, skrótem, typem, statusem wykonania
- Prosta kontrola dostępu poprzez login/hasło
- Stylowanie i oznaczenia kolorystyczne typów zadań

## 📦 Struktura projektu

------------------------------------------------------------------------

## 🧪 Testowanie lokalne

1. Skonfiguruj środowisko XAMPP/LAMP/WAMP
2. Utwórz bazę danych i zaimportuj `db.sql`
3. Uzupełnij dane logowania do bazy w `tasks.php` i `login.php`
4. Uruchom projekt lokalnie przez `localhost`

## 🔒 Bezpieczeństwo

- Aplikacja wykorzystuje przygotowane zapytania (prepared statements)
- Sesje PHP zabezpieczają dostęp do kalendarza
- Zalecane: implementacja hashowania haseł (np. `password_hash`)

## 📌 Zastosowanie

- Organizacja rodzinnych obowiązków
- Prosty CRM zespołu
- Przykład do portfolio (frontend + backend + logika CRUD)

## 🧑‍💻 Autor

Projekt stworzony przez [Mateusz Biernacki] jako część portfolio programistycznego.

