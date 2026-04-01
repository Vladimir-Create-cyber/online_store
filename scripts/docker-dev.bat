@echo off
setlocal
cd /d "%~dp0.."
docker compose up -d --build
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d vite
endlocal
