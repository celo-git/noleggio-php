# Noleggio Car Rental System

This is a basic PHP project structure for a car rental (noleggio) management system.

## Project Structure
- `public/` - Publicly accessible files (entry point: `index.php`)
- `src/` - Application source code
- `config/` - Configuration files

## Getting Started

1. Install [Composer](https://getcomposer.org/).
2. Run `composer install` in the project root to install dependencies.
3. Copy `config/config.sample.php` to `config/config.php` and update with your settings.
4. Avvia il server PHP integrato (ad esempio tramite XAMPP):
   ```powershell
   C:\xampp\php\php.exe -S localhost:8000 -t public
   ```
5. Apri [http://localhost:8000](http://localhost:8000) nel browser.

## Gestione delle dipendenze

- Per installare le dipendenze definite in `composer.json`:

   ```powershell
   composer install
   ```

- Per aggiungere nuove dipendenze:

   ```powershell
   composer require nome/pacchetto
   ```
