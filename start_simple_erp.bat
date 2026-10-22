@echo off
title Simple HTML/CSS College ERP (SQL Server)
echo ========================================================
echo   Starting Simple College ERP System (HTML + CSS + SQL)...
echo ========================================================
echo.

echo Starting Local Web Server on http://localhost:8080...
start "" "C:\Program Files\IIS Express\iisexpress.exe" /path:"%~dp0StudentAcademicAdmin" /port:8080

timeout /t 2 >nul

echo Opening Browser...
start http://localhost:8080/Login.aspx

echo.
echo ========================================================
echo  Simple ERP is running!
echo  URL:         http://localhost:8080/Login.aspx
echo  Username:    vartika
echo  Password:    vartika123
echo ========================================================
pause
