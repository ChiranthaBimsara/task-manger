@echo off
setlocal

:: --- STEP 1: FIND PHP ---
set PHP_CMD=php

:: Check if php is already in global PATH
where php >nul 2>nul
if %ERRORLEVEL% EQU 0 goto :CHECK_DEPS

:: Check common installation paths (Add yours if different)
if exist "C:\xampp\php\php.exe" set PHP_CMD="C:\xampp\php\php.exe" & goto :CHECK_DEPS
if exist "D:\xampp\php\php.exe" set PHP_CMD="D:\xampp\php\php.exe" & goto :CHECK_DEPS
if exist "C:\php\php.exe" set PHP_CMD="C:\php\php.exe" & goto :CHECK_DEPS
if exist "C:\Program Files\php\php.exe" set PHP_CMD="C:\Program Files\php\php.exe" & goto :CHECK_DEPS
if exist "C:\wamp64\bin\php\php7.3.12\php.exe" set PHP_CMD="C:\wamp64\bin\php\php7.3.12\php.exe" & goto :CHECK_DEPS

echo [ERROR] PHP could not be found automatically.
echo.
echo Please locate your 'php.exe'. It is usually in a 'php' folder inside xampp, wamp, or a standalone installation.
set /p MANUAL_PATH="Paste the full path to php.exe and press Enter: "


:: Remove quotes from input if present
set MANUAL_PATH=%MANUAL_PATH:"=%

if not exist "%MANUAL_PATH%" (
    echo [ERROR] The file "%MANUAL_PATH%" does not exist.
    pause
    exit /b
)
set PHP_CMD="%MANUAL_PATH%"

:CHECK_DEPS
:: --- STEP 2: INSTALL DEPENDENCIES IF MISSING ---
if exist "vendor\autoload.php" goto :START_SERVER

echo [INFO] Vendor folder missing. Installing Composer dependencies...
if not exist "composer.phar" (
    %PHP_CMD% -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    %PHP_CMD% composer-setup.php
    %PHP_CMD% -r "unlink('composer-setup.php');"
)
%PHP_CMD% composer.phar install

:START_SERVER
:: --- STEP 3: START SERVER ---
echo.
echo [SUCCESS] Server running at http://localhost:8000
echo Press Ctrl+C to stop.
%PHP_CMD% -S localhost:8000 -t public