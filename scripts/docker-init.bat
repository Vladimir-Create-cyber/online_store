@echo off
setlocal
cd /d "%~dp0.."
docker compose exec app php artisan key:generate
docker compose exec app sh -c "if [ ! -L public/storage ]; then rm -rf public/storage && php artisan storage:link; fi"
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:ensure-product-images --force
docker compose exec app php artisan products:sync-demo-locales
docker compose exec app php artisan products:fetch-gallery --force
endlocal
