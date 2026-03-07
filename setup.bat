@echo off
setlocal

:: --- FIND PHP ---
set PHP_CMD=php
where php >nul 2>nul
if %ERRORLEVEL% EQU 0 goto :FOUND_PHP
if exist "C:\wamp64\bin\php\php7.3.12\php.exe" set PHP_CMD="C:\wamp64\bin\php\php7.3.12\php.exe" & goto :FOUND_PHP
if exist "C:\xampp\php\php.exe" set PHP_CMD="C:\xampp\php\php.exe" & goto :FOUND_PHP

:FOUND_PHP
echo Using PHP at: %PHP_CMD%

echo.
echo --- Fixing Directory Structure...
if not exist "src\Config" mkdir src\Config
if not exist "src\Models" mkdir src\Models
if not exist "src\Controllers" mkdir src\Controllers

if exist Database.php move Database.php src\Config\
if exist User.php move User.php src\Models\
if exist Task.php move Task.php src\Models\
if exist AuthController.php move AuthController.php src\Controllers\
if exist TaskController.php move TaskController.php src\Controllers\

echo.
echo --- Checking Dependencies...
if not exist "composer.phar" (
    %PHP_CMD% -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    %PHP_CMD% composer-setup.php
    %PHP_CMD% -r "unlink('composer-setup.php');"
)
%PHP_CMD% composer.phar install

echo.
echo --- Done! You can now run the server.
pause
