# Deploy a producción — sistema.inforsyspdv.com

Ejecuta el deploy completo en el VPS de producción.

## Pasos que debes seguir

1. Mostrá al usuario los comandos que se van a ejecutar y pedí confirmación antes de proceder.

2. Una vez confirmado, ejecutá los siguientes comandos via SSH en el servidor `sistema.inforsyspdv.com` (IP: `187.127.0.232`, ruta del proyecto: `/var/www/inforsys-web`):

```bash
cd /var/www/inforsys-web && git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

3. Si el argumento `$ARGUMENTS` contiene `--full` o `full`, agregá también:
```bash
php artisan queue:restart
```

4. Reportá el resultado de cada paso indicando si fue exitoso o si hubo algún error.

5. Al finalizar, mostrá la URL de acceso: https://sistema.inforsyspdv.com/admin

## Notas importantes
- Si `git pull` falla por "dubious ownership": `git config --global --add safe.directory /var/www/inforsys-web`
- Si hay error en PDF export (500): `sudo apt-get install php8.2-gd -y && sudo systemctl restart php8.2-fpm`
- La extensión PHP GD debe estar instalada para que funcione la exportación PDF con dompdf
