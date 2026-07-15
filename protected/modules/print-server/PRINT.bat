@echo off
setlocal

:loop

rem Set the PHP path
set PHP_PATH=%~dp0php\php.exe

rem Set the project path
set PROJECT_PATH=%~dp0project

"%PHP_PATH%" "server.php"

REM Check the exit code of the PHP script
if ERRORLEVEL 1 (
    echo WebSocket server crashed or exited with an error. Restarting in 5 seconds...
    timeout /t 5 /nobreak >nul
    goto loop
) else (
    echo WebSocket server stopped normally.
)

endlocal
pause
